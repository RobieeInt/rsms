<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\VisitReport;
use App\Notifications\VisitReportSentNotification;
use Livewire\Component;
use Livewire\WithPagination;

class ReportList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function sendEmail(int $id): void
    {
        $report = VisitReport::with(['client', 'technician', 'schedule'])->findOrFail($id);

        if (!$report->client->pic_email) {
            $this->dispatch('notify', message: 'Klien tidak memiliki alamat email.', type: 'error');
            return;
        }

        $report->client->notifyNow(new VisitReportSentNotification($report));
        $this->dispatch('notify', message: 'Laporan ' . $report->report_number . ' berhasil dikirim ke ' . $report->client->pic_email . '.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();

        $reports = VisitReport::with(['client', 'technician', 'schedule'])
            ->when($user->hasRole('technician'), fn($q) => $q->where('technician_id', $user->id))
            ->when($this->clientFilter, fn($q) => $q->where('client_id', $this->clientFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where('report_number', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn($q) => $q->where('company_name', 'like', "%{$this->search}%")))
            ->orderByDesc('created_at')
            ->paginate(15);

        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        return view('livewire.reports.report-list', compact('reports', 'clients'))
            ->layout('layouts.app', ['title' => 'Visit Reports']);
    }
}
