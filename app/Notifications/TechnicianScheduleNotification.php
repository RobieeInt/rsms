<?php

namespace App\Notifications;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TechnicianScheduleNotification extends Notification
{
    public function __construct(
        public Schedule $schedule,
        public string $type = 'created' // created | reminder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->type === 'reminder' ? 'Reminder Kunjungan Besok' : 'Jadwal Kunjungan Baru';
        return [
            'type'        => 'technician_schedule_' . $this->type,
            'title'       => $label,
            'message'     => $this->schedule->client->company_name . ' — ' . $this->schedule->visit_date->format('d M Y') . ' ' . $this->schedule->start_time,
            'schedule_id' => $this->schedule->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Carbon::setLocale('id');

        $schedule = $this->schedule;
        $date     = $schedule->visit_date->locale('id')->translatedFormat('l, d F Y');
        $time     = $schedule->start_time
            . ($schedule->end_time ? ' — ' . $schedule->end_time : '');
        $client   = $schedule->client->company_name;
        $address  = $schedule->client->address ?? '-';

        if ($this->type === 'reminder') {
            $subject = "⏰ Reminder Kunjungan Besok — {$client}";
            $intro   = 'Ini adalah pengingat bahwa kamu memiliki jadwal kunjungan **besok**.';
        } else {
            $subject = "Jadwal Kunjungan Baru — {$client}";
            $intro   = 'Kamu mendapatkan jadwal kunjungan IT maintenance baru.';
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($intro)
            ->line('**Klien:** ' . $client)
            ->line('**Tanggal:** ' . $date)
            ->line('**Waktu:** ' . $time)
            ->line('**Alamat:** ' . $address);

        if ($schedule->notes) {
            $mail->line('**Catatan:** ' . $schedule->notes);
        }

        return $mail->salutation('Terima kasih, Reconext Digital Kreasi');
    }

    public static function waClientText(Schedule $schedule): string
    {
        Carbon::setLocale('id');

        $date      = $schedule->visit_date->locale('id')->translatedFormat('l, d F Y');
        $time      = $schedule->start_time . ($schedule->end_time ? ' s/d ' . $schedule->end_time : '');
        $techName  = $schedule->technician->name ?? 'Teknisi';
        $notes     = $schedule->notes ? "\n📝 *Catatan:* {$schedule->notes}" : '';

        return "🛠️ *Pemberitahuan Kunjungan Teknisi*\n\n"
            . "Yth. {$schedule->client->pic_name},\n\n"
            . "Kami informasikan bahwa teknisi akan datang untuk kunjungan service dengan detail:\n\n"
            . "👤 *Teknisi:* {$techName}\n"
            . "📆 *Tanggal:* {$date}\n"
            . "🕐 *Perkiraan Waktu:* {$time}"
            . $notes . "\n\n"
            . "Terima kasih.\n_Reconext Digital Kreasi_";
    }

    public static function waText(Schedule $schedule, string $type = 'created'): string
    {
        Carbon::setLocale('id');

        $date   = $schedule->visit_date->locale('id')->translatedFormat('l, d F Y');
        $time   = $schedule->start_time . ($schedule->end_time ? ' s/d ' . $schedule->end_time : '');
        $client = $schedule->client->company_name;
        $addr   = $schedule->client->address ?? '-';
        $notes  = $schedule->notes ? "\n📝 *Catatan:* {$schedule->notes}" : '';

        if ($type === 'reminder') {
            $header = "⏰ *REMINDER — Kunjungan Besok!*\n\n";
        } else {
            $header = "📅 *Jadwal Kunjungan Baru*\n\n";
        }

        return $header
            . "Halo {$schedule->technician->name},\n\n"
            . "🏢 *Klien:* {$client}\n"
            . "📆 *Tanggal:* {$date}\n"
            . "🕐 *Waktu:* {$time}\n"
            . "📍 *Alamat:* {$addr}"
            . $notes . "\n\n"
            . "Mohon hadir tepat waktu.\n_Reconext Digital Kreasi_";
    }
}
