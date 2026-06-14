<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quotation;
use App\Notifications\InvoiceGeneratedNotification;
use Illuminate\Support\Facades\DB;

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
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'sort_order' => $item->sort_order,
                ]);
            }

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
                $this->markAsSent($invoice);
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
