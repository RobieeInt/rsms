<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGeneratedNotification extends Notification
{
    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'invoice_generated',
            'title'      => 'Invoice Dikirim',
            'message'    => 'Invoice ' . $this->invoice->invoice_number . ' telah dikirim ke klien.',
            'invoice_id' => $this->invoice->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $invoice = $this->invoice;
        $amount  = 'Rp ' . number_format($invoice->total_amount, 0, ',', '.');
        $due     = $invoice->due_date->locale('id')->translatedFormat('d F Y');

        $desc = $invoice->type === 'retainer'
            ? 'Layanan IT Support Bulanan — ' . $invoice->invoice_date->locale('id')->translatedFormat('F Y')
            : ($invoice->quotation?->title ?? 'Layanan IT');

        $filename = $invoice->invoice_number . '.pdf';
        $pdfOutput = app(PdfService::class)->generateInvoice($invoice)->output();

        return (new MailMessage)
            ->subject('Invoice ' . $invoice->invoice_number . ' — Reconext Digital Kreasi')
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line('Berikut kami sampaikan invoice dari **Reconext Digital Kreasi**.')
            ->line('**No. Invoice:** ' . $invoice->invoice_number)
            ->line('**Keterangan:** ' . $desc)
            ->line('**Jumlah:** ' . $amount)
            ->line('**Jatuh Tempo:** ' . $due)
            ->line('Invoice terlampir dalam email ini sebagai file PDF.')
            ->line('Mohon melakukan pembayaran sebelum tanggal jatuh tempo.')
            ->salutation('Terima kasih, Reconext Digital Kreasi')
            ->attachData($pdfOutput, $filename, ['mime' => 'application/pdf']);
    }
}
