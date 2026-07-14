<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Notifications\TechnicianScheduleNotification;
use Illuminate\Console\Command;
use Throwable;

class SendScheduleReminders extends Command
{
    protected $signature = 'schedules:send-reminders';
    protected $description = 'Send H-1 reminder to technicians for tomorrow\'s visits';

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Schedule::with(['client', 'technician'])
            ->where('visit_date', $tomorrow)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->each(function (Schedule $schedule) {
                try {
                    $schedule->technician->notifyNow(
                        new TechnicianScheduleNotification($schedule, 'reminder')
                    );
                    $this->line("  [reminder] {$schedule->technician->name} — {$schedule->client->company_name}");
                } catch (Throwable $e) {
                    $this->error("  [reminder] {$schedule->technician->name} FAILED: ".$e->getMessage());
                }
            });

        $this->info('Done.');
    }
}
