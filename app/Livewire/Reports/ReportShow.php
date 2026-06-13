<?php

namespace App\Livewire\Reports;

use App\Models\VisitReport;
use App\Notifications\VisitReportSentNotification;
use Livewire\Component;

class ReportShow extends Component
{
    public VisitReport $report;

    public function mount(VisitReport $report): void
    {
        $this->report = $report;
    }

    public function sendEmail(): void
    {
        $this->report->load(['client', 'technician', 'schedule']);

        if (!$this->report->client->pic_email) {
            $this->dispatch('notify', message: 'Klien tidak memiliki alamat email.', type: 'error');
            return;
        }

        $this->report->client->notifyNow(new VisitReportSentNotification($this->report));
        $this->dispatch('notify', message: 'Laporan berhasil dikirim ke ' . $this->report->client->pic_email . '.', type: 'success');
    }

    public function render()
    {
        $this->report->load([
            'client', 'technician', 'schedule',
            'assetChecklists.asset', 'networkChecklist',
            'findings', 'photos',
        ]);

        return view('livewire.reports.report-show')
            ->layout('layouts.app', ['title' => $this->report->report_number]);
    }
}
