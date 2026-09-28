<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Notifications\InvoiceGeneratedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InvoiceService
{
    public function createFromRetainer(Client $client, int $createdBy): Invoice
    {
        return DB::transaction(function () use ($client, $createdBy) {
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'created_by' => $createdBy,
                'invoice_number' => Invoice::generateNumber(),
                'type' => 'retainer',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($client->invoice_due_date)->toDateString(),
                'subtotal' => $client->monthly_retainer_fee,
                'total_amount' => $client->monthly_retainer_fee,
                'status' => 'draft',
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Monthly IT Support Retainer - ' . now()->format('F Y'),
                'quantity' => 1,
                'unit' => 'month',
                'unit_price' => $client->monthly_retainer_fee,
                'cost_price' => $client->retainer_cost,
                'total_price' => $client->monthly_retainer_fee,
            ]);

            return $invoice;
        });
    }

    public function createFromQuotation(Quotation $quotation, int $createdBy): Invoice
    {
        return DB::transaction(function () use ($quotation, $createdBy) {
            $invoice = Invoice::create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'created_by' => $createdBy,
                'invoice_number' => Invoice::generateNumber(),
                'type' => 'quotation',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($quotation->client->invoice_due_date)->toDateString(),
                'subtotal' => $quotation->subtotal,
                'tax_percent' => $quotation->tax_percent,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total_amount' => $quotation->total_amount,
                'status' => 'draft',
            ]);

            foreach ($quotation->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'detail' => $item->detail,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'total_price' => $item->total_price,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Invoice dibuat otomatis saat klien approve penawaran: kalau penawaran
     * punya skema termin, cuma termin pertama yang ditagih; kalau nggak,
     * invoice penuh (line item di-copy 1:1) seperti biasa.
     */
    public function createForApprovedQuotation(Quotation $quotation, int $createdBy): Invoice
    {
        $terms = $quotation->scheduledTerms();

        if (count($terms) > 1) {
            $first = $terms[0];

            return $this->createInstallmentFromQuotation(
                $quotation,
                $createdBy,
                $first['amount'],
                sprintf('%s (%s%%) — %s', $first['label'], Quotation::formatPercent($first['percent']), $quotation->quotation_number)
            );
        }

        return $this->createFromQuotation($quotation, $createdBy);
    }

    /**
     * Creates one independent invoice for a partial (or full) slice of a
     * quotation's remaining balance — used for installment/termin billing.
     * Unlike createFromQuotation(), this does not copy the quotation's line
     * items 1:1 (an arbitrary partial amount can't be meaningfully prorated
     * across them), so it builds a single line item for the given amount.
     * The caller is responsible for validating $amount against the
     * quotation's remaining balance before calling this.
     */
    public function createInstallmentFromQuotation(
        Quotation $quotation,
        int $createdBy,
        float $amount,
        ?string $description = null
    ): Invoice {
        return DB::transaction(function () use ($quotation, $createdBy, $amount, $description) {
            // Cancelled invoices don't count as a termin (they're also excluded
            // from remainingBalance()), so a re-issued termin keeps its number.
            $installmentNumber = $quotation->invoices()->where('status', '!=', 'cancelled')->count() + 1;

            $invoice = Invoice::create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'installment_number' => $installmentNumber,
                'created_by' => $createdBy,
                'invoice_number' => Invoice::generateNumber(),
                'type' => 'quotation',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($quotation->client->invoice_due_date)->toDateString(),
                'subtotal' => $amount,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $amount,
                'notes' => $description,
                'status' => 'draft',
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $description
                    ?: sprintf('Termin ke-%d — %s', $installmentNumber, $quotation->quotation_number),
                'quantity' => 1,
                'unit' => 'termin',
                'unit_price' => $amount,
                'discount_amount' => 0,
                'total_price' => $amount,
                'sort_order' => 0,
            ]);

            return $invoice;
        });
    }

    public function markAsSent(Invoice $invoice): void
    {
        $invoice->update(['status' => 'sent']);

        $email = $invoice->client->pic_email ?? null;
        if ($email) {
            $invoice->client->notifyNow(new InvoiceGeneratedNotification($invoice));
        }
        $invoice->logSend('sent', $email);
    }

    public function markAsPaid(Invoice $invoice, array $data): void
    {
        $invoice->update([
            'status' => 'paid',
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'payment_proof' => $data['payment_proof'] ?? null,
            'payment_notes' => $data['payment_notes'] ?? null,
        ]);
    }

    public function generateMonthlyInvoices(): void
    {
        $clients = Client::where('is_active', true)->get();

        foreach ($clients as $client) {
            if ($client->monthly_retainer_fee <= 0) {
                continue;
            }

            $exists = Invoice::where('client_id', $client->id)
                ->where('type', 'retainer')
                ->whereYear('invoice_date', now()->year)
                ->whereMonth('invoice_date', now()->month)
                ->exists();

            if (!$exists) {
                $invoice = $this->createFromRetainer($client, 1);
                try {
                    $this->markAsSent($invoice);
                } catch (Throwable $e) {
                    Log::warning("Invoice retainer {$client->company_name} dibuat tapi gagal kirim email: ".$e->getMessage());

                    // A silently-undelivered retainer invoice is easy to
                    // miss if it's only logged — surface it to admins too.
                    // Guarded separately so a failure here can't also
                    // interrupt the loop over the remaining clients.
                    try {
                        $notif = new AdminAlertNotification(
                            'Invoice Gagal Terkirim',
                            "Invoice retainer {$invoice->invoice_number} untuk {$client->company_name} dibuat tapi emailnya gagal terkirim otomatis.",
                            'warning'
                        );
                        User::role('admin')->get()->each(fn ($admin) => $admin->notifyNow($notif));
                    } catch (Throwable $e2) {
                        Log::warning('Gagal mengirim alert admin untuk invoice yang gagal terkirim: '.$e2->getMessage());
                    }
                }
            }
        }
    }

    public function updateOverdueStatus(): void
    {
        Invoice::where('status', 'sent')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }
}
