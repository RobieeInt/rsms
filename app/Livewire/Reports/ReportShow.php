<?php

namespace App\Livewire\Reports;

use App\Models\VisitReport;
use Livewire\Component;

class ReportShow extends Component
{
    public VisitReport $report;

    public function mount(VisitReport $report): void
    {
        $this->report = $report;
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
