<?php

namespace App\Notifications;

use App\Models\VisitReport;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitReportSentNotification extends Notification
{
    public function __construct(public VisitReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $report   = $this->report;
        $schedule = $report->schedule;
        $filename = $report->report_number . '.pdf';
        $pdf      = app(PdfService::class)->generateVisitReport($report)->output();

        $mail = (new MailMessage)
            ->subject('Laporan Kunjungan ' . $report->report_number . ' — Reconext Digital Kreasi')
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line('Berikut adalah laporan kunjungan maintenance IT yang telah selesai dilaksanakan.')
            ->line('**No. Laporan:** ' . $report->report_number)
            ->line('**Tanggal Kunjungan:** ' . $schedule->visit_date->locale('id')->translatedFormat('d F Y'))
            ->line('**Teknisi:** ' . $report->technician->name);

        if ($report->summary) {
            $mail->line('**Ringkasan:** ' . $report->summary);
        }

        return $mail
            ->action('Download Laporan PDF', $report->getPublicPdfUrl())
            ->line('Laporan juga terlampir dalam email ini sebagai file PDF.')
            ->salutation('Terima kasih, Reconext Digital Kreasi')
            ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'visit_report_sent',
            'title'     => 'Laporan Kunjungan Dikirim',
            'message'   => 'Laporan ' . $this->report->report_number . ' telah dikirim ke klien.',
            'report_id' => $this->report->id,
        ];
    }
}
