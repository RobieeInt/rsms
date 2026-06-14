<?php

namespace App\Notifications;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleCreatedNotification extends Notification
{
    public function __construct(public Schedule $schedule) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $schedule = $this->schedule;
        $date     = $schedule->visit_date->locale('id')->translatedFormat('l, d F Y');
        $time     = $schedule->start_time
            . ($schedule->end_time ? ' — ' . $schedule->end_time : '');

        $mail = (new MailMessage)
            ->subject('Jadwal Kunjungan IT Maintenance — ' . $schedule->visit_date->locale('id')->translatedFormat('d F Y'))
            ->greeting('Yth. ' . ($notifiable->pic_name ?? 'Bapak/Ibu') . ',')
            ->line('Kami ingin menginformasikan jadwal kunjungan IT maintenance dari **Reconext Digital Kreasi**.')
            ->line('**Tanggal:** ' . $date)
            ->line('**Waktu:** ' . $time)
            ->line('**Teknisi:** ' . $schedule->technician->name);

        if ($schedule->notes) {
            $mail->line('**Catatan:** ' . $schedule->notes);
        }

        return $mail
            ->line('Mohon pastikan akses tersedia pada waktu yang telah ditentukan.')
            ->salutation('Terima kasih, Reconext Digital Kreasi');
    }
}
