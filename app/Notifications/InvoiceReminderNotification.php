<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public string $reminderType
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->reminderType) {
            '7_days_before' => 'Payment Reminder - 7 Days Until Due',
            '3_days_before' => 'Payment Reminder - 3 Days Until Due',
            'due_today' => 'Payment Due Today - ' . $this->invoice->invoice_number,
            '7_days_overdue' => 'OVERDUE - Invoice ' . $this->invoice->invoice_number,
            default => 'Invoice Payment Reminder',
        };

        $message = match ($this->reminderType) {
            '7_days_before' => 'Your invoice is due in 7 days.',
            '3_days_before' => 'Your invoice is due in 3 days.',
            'due_today' => 'Your invoice payment is due today.',
            '7_days_overdue' => 'Your invoice is 7 days overdue. Please arrange payment immediately.',
            default => 'Please review your invoice.',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . $notifiable->pic_name . ',')
            ->line($message)
            ->line('**Invoice Number:** ' . $this->invoice->invoice_number)
            ->line('**Amount:** Rp ' . number_format($this->invoice->total_amount, 0, ',', '.'))
            ->line('**Due Date:** ' . $this->invoice->due_date->format('d F Y'))
            ->line('Please arrange payment at your earliest convenience.')
            ->salutation('Best regards, PT Reconext IT Solutions');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_reminder',
            'title' => 'Invoice Payment Reminder',
            'message' => 'Invoice ' . $this->invoice->invoice_number . ' payment reminder.',
            'invoice_id' => $this->invoice->id,
            'reminder_type' => $this->reminderType,
        ];
    }
}
