<?php

namespace App\Notifications;

use App\Models\Quotation;
use App\Services\PdfService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationSentNotification extends Notification
{
    public function __construct(public Quotation $quotation) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $q        = $this->quotation;
        $amount   = 'Rp ' . number_format($q->total_amount, 0, ',', '.');
        $valid    = $q->expiry_date->format('d F Y');
        $filename = $q->quotation_number . '.pdf';
        $pdf      = app(PdfService::class)->generateQuotation($q)->output();

        return (new MailMessage)
            ->subject('Penawaran ' . $q->quotation_number . ' — Reconext Digital Kreasi')
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line('Berikut kami sampaikan penawaran harga dari **Reconext Digital Kreasi**.')
            ->line('**No. Penawaran:** ' . $q->quotation_number)
            ->line('**Total:** ' . $amount)
            ->line('**Berlaku Hingga:** ' . $valid)
            ->line('Dokumen penawaran terlampir dalam email ini. Klik tombol di bawah untuk mereview dan memberikan persetujuan.')
            ->action('Review & Setujui Penawaran', $q->getApprovalUrl())
            ->salutation('Terima kasih, Reconext Digital Kreasi')
            ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'quotation_sent',
            'title'        => 'Penawaran Dikirim',
            'message'      => 'Penawaran ' . $this->quotation->quotation_number . ' telah dikirim ke klien.',
            'quotation_id' => $this->quotation->id,
        ];
    }
}
