<?php

namespace App\Services;

use App\Models\VisitReport;
use App\Notifications\VisitReportSentNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportService
{
    /**
     * A visit report has no manual draft/completed toggle — it only ever
     * becomes 'completed' as a reflection of its schedule being checked out.
     * Call this after saving a report AND after a schedule check-out, since
     * either one can happen first. The client is emailed the first time the
     * report transitions into 'completed'.
     */
    public function syncStatusWithSchedule(VisitReport $report): void
    {
        $report->loadMissing(['schedule', 'client']);

        if ($report->schedule?->status !== 'completed' || $report->status === 'completed') {
            return;
        }

        $report->update(['status' => 'completed']);
        $this->notifyClient($report);
    }

    private function notifyClient(VisitReport $report): void
    {
        if (! $report->client || ! $report->client->pic_email) {
            return;
        }

        try {
            $report->client->notifyNow(new VisitReportSentNotification($report));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim email laporan otomatis: '.$e->getMessage());
        }
    }
}
