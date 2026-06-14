<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Auto-mark overdue invoices and send payment reminder emails';

    public function handle(): void
    {
        // Mark invoices past due_date as overdue
        $marked = Invoice::whereIn('status', ['sent'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        if ($marked) {
            $this->info("Marked {$marked} invoice(s) as overdue.");
        }

        // Upcoming reminders: 7 days before, 3 days before, due today
        $upcoming = [
            '7_days_before' => now()->addDays(7)->toDateString(),
            '3_days_before' => now()->addDays(3)->toDateString(),
            'due_today'     => now()->toDateString(),
        ];

        foreach ($upcoming as $type => $dueDate) {
            Invoice::with('client')
                ->whereIn('status', ['sent', 'overdue'])
                ->where('due_date', $dueDate)
                ->whereHas('client', fn($q) => $q->whereNotNull('pic_email'))
                ->each(function (Invoice $invoice) use ($type) {
                    $invoice->client->notifyNow(new InvoiceReminderNotification($invoice, $type));
                    $this->line("  [{$type}] {$invoice->invoice_number} → {$invoice->client->pic_email}");
                });
        }

        // 7-day overdue reminder
        Invoice::with('client')
            ->where('status', 'overdue')
            ->where('due_date', now()->subDays(7)->toDateString())
            ->whereHas('client', fn($q) => $q->whereNotNull('pic_email'))
            ->each(function (Invoice $invoice) {
                $invoice->client->notifyNow(new InvoiceReminderNotification($invoice, '7_days_overdue'));
                $this->line("  [7_days_overdue] {$invoice->invoice_number} → {$invoice->client->pic_email}");
            });

        $this->info('Done.');
    }
}
