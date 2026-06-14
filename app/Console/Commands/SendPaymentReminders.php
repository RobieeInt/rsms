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
            'reminder_7days' => now()->addDays(7)->toDateString(),
            'reminder_3days' => now()->addDays(3)->toDateString(),
            'due_today'      => now()->toDateString(),
        ];

        foreach ($upcoming as $type => $dueDate) {
            Invoice::with('client')
                ->whereIn('status', ['sent', 'overdue'])
                ->where('due_date', $dueDate)
                ->whereHas('client', fn($q) => $q->whereNotNull('pic_email'))
                ->each(function (Invoice $invoice) use ($type) {
                    $email = $invoice->client->pic_email;
                    $invoice->client->notifyNow(new InvoiceReminderNotification($invoice, $type));
                    $invoice->logSend($type, $email);
                    $this->line("  [{$type}] {$invoice->invoice_number} → {$email}");
                });
        }

        // 7-day overdue reminder
        Invoice::with('client')
            ->where('status', 'overdue')
            ->where('due_date', now()->subDays(7)->toDateString())
            ->whereHas('client', fn($q) => $q->whereNotNull('pic_email'))
            ->each(function (Invoice $invoice) {
                $email = $invoice->client->pic_email;
                $invoice->client->notifyNow(new InvoiceReminderNotification($invoice, 'overdue_7days'));
                $invoice->logSend('overdue_7days', $email);
                $this->line("  [overdue_7days] {$invoice->invoice_number} → {$email}");
            });

        $this->info('Done.');
    }
}
