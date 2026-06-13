<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Schedule $schedule) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reconext IT Support Visit Schedule')
            ->greeting('Dear ' . $notifiable->pic_name . ',')
            ->line('We have scheduled a maintenance visit for your company.')
            ->line('**Visit Date:** ' . $this->schedule->visit_date->format('d F Y'))
            ->line('**Time:** ' . $this->schedule->start_time)
            ->line('**Technician:** ' . $this->schedule->technician->name)
            ->when($this->schedule->notes, fn($mail) => $mail->line('**Notes:** ' . $this->schedule->notes))
            ->line('Please ensure access is available at the scheduled time.')
            ->salutation('Best regards, PT Reconext IT Solutions');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'schedule_created',
            'title' => 'Visit Scheduled',
            'message' => 'A maintenance visit has been scheduled for ' . $this->schedule->visit_date->format('d F Y'),
            'schedule_id' => $this->schedule->id,
        ];
    }
}
