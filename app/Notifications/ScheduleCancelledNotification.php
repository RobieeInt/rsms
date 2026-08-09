<?php

namespace App\Notifications;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleCancelledNotification extends Notification
{
    public function __construct(public Schedule $schedule) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'schedule_cancelled',
            'title'       => 'Jadwal Kunjungan Dibatalkan',
            'message'     => 'Jadwal kunjungan ke ' . $this->schedule->client->company_name . ' pada ' . $this->schedule->visit_date->format('d M Y') . ' telah dibatalkan.',
            'schedule_id' => $this->schedule->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $schedule = $this->schedule;
        $date     = $schedule->visit_date->locale('id')->translatedFormat('l, d F Y');

        return (new MailMessage)
            ->subject('Jadwal Kunjungan Dibatalkan — ' . $schedule->visit_date->locale('id')->translatedFormat('d F Y'))
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line('Kami informasikan bahwa jadwal kunjungan IT maintenance dari **Reconext Digital Kreasi** berikut telah dibatalkan.')
            ->line('**Tanggal:** ' . $date)
            ->line('Kami akan menghubungi kembali untuk penjadwalan ulang jika diperlukan.')
            ->salutation('Terima kasih, Reconext Digital Kreasi');
    }
}
