<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quotation $quotation) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quotation ' . $this->quotation->quotation_number . ' - Reconext IT Solutions')
            ->greeting('Dear ' . $notifiable->pic_name . ',')
            ->line('Please find our quotation for your review.')
            ->line('**Quotation Number:** ' . $this->quotation->quotation_number)
            ->line('**Amount:** Rp ' . number_format($this->quotation->total_amount, 0, ',', '.'))
            ->line('**Valid Until:** ' . $this->quotation->expiry_date->format('d F Y'))
            ->action('Review & Approve Quotation', $this->quotation->getApprovalUrl())
            ->line('Please click the button above to review and approve or reject the quotation.')
            ->salutation('Best regards, PT Reconext IT Solutions');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'quotation_sent',
            'title' => 'Quotation Sent',
            'message' => 'Quotation ' . $this->quotation->quotation_number . ' has been sent.',
            'quotation_id' => $this->quotation->id,
        ];
    }
}
