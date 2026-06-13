<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceReminderNotification;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send payment reminder notifications for outstanding invoices';

    public function handle(): void
    {
        $this->info('Sending payment reminders...');

        $reminders = [
            '7_days_before' => now()->addDays(7)->toDateString(),
            '3_days_before' => now()->addDays(3)->toDateString(),
            'due_today' => now()->toDateString(),
        ];

        foreach ($reminders as $type => $dueDate) {
            Invoice::with('client')
                ->whereIn('status', ['sent', 'overdue'])
                ->where('due_date', $dueDate)
                ->each(function (Invoice $invoice) use ($type) {
                    $invoice->client->notify(new InvoiceReminderNotification($invoice, $type));
                    $this->line("Sent {$type} reminder for invoice {$invoice->invoice_number}");
                });
        }

        Invoice::with('client')
            ->where('status', 'overdue')
            ->where('due_date', now()->subDays(7)->toDateString())
            ->each(function (Invoice $invoice) {
                $invoice->client->notify(new InvoiceReminderNotification($invoice, '7_days_overdue'));
                $this->line("Sent 7_days_overdue reminder for invoice {$invoice->invoice_number}");
            });

        $this->info('Done.');
    }
}
