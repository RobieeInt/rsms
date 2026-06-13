<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice ' . $this->invoice->invoice_number . ' - Reconext Digital Kreasi')
            ->greeting('Dear ' . $notifiable->pic_name . ',')
            ->line('Please find your invoice details below.')
            ->line('**Invoice Number:** ' . $this->invoice->invoice_number)
            ->line('**Description:** Invoice for ' . $this->invoice->quotation->title)
            ->line('**Amount:** Rp ' . number_format($this->invoice->total_amount, 0, ',', '.'))
            ->line('**Due Date:** ' . $this->invoice->due_date->format('d F Y'))
            ->action('View Invoice', url('/'))
            ->line('Thank you for your business.')
            ->salutation('Best regards, Reconext Digital Kreasi');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_generated',
            'title' => 'Invoice Generated',
            'message' => 'Invoice ' . $this->invoice->invoice_number . ' has been generated.',
            'invoice_id' => $this->invoice->id,
        ];
    }
}
