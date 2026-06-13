<?php

namespace App\Notifications;

use App\Models\VisitReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitReportSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public VisitReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $report   = $this->report;
        $schedule = $report->schedule;

        return (new MailMessage)
            ->subject('Laporan Kunjungan ' . $report->report_number . ' - Reconext Digital Kreasi')
            ->greeting('Dear ' . $notifiable->pic_name . ',')
            ->line('Berikut adalah laporan kunjungan maintenance IT yang telah selesai dilaksanakan.')
            ->line('**No. Laporan:** ' . $report->report_number)
            ->line('**Tanggal Kunjungan:** ' . $schedule->visit_date->locale('id')->translatedFormat('d F Y'))
            ->line('**Teknisi:** ' . $report->technician->name)
            ->when($report->summary, fn($m) => $m->line('**Ringkasan:** ' . $report->summary))
            ->action('Lihat Laporan', route('reports.show', $report))
            ->line('Terima kasih atas kepercayaan Anda kepada layanan kami.')
            ->salutation('Salam, Reconext Digital Kreasi');
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
