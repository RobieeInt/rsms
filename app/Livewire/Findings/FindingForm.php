<?php

namespace App\Livewire\Findings;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Finding;
use App\Models\VisitReport;
use Livewire\Component;

class FindingForm extends Component
{
    public ?Finding $finding = null;

    public int $client_id = 0;
    public int $asset_id = 0;
    public int $visit_report_id = 0;
    public string $title = '';
    public string $description = '';
    public string $category = '';
    public string $severity = 'medium';
    public string $status = 'open';

    public array $clientAssets = [];
    public array $clientReports = [];

    public function mount(?Finding $finding = null): void
    {
        if ($finding && $finding->exists) {
            $this->finding = $finding;
            $this->client_id = $finding->client_id;
            $this->asset_id = $finding->asset_id ?? 0;
            $this->visit_report_id = $finding->visit_report_id ?? 0;
            $this->title = $finding->title;
            $this->description = $finding->description ?? '';
            $this->category = $finding->category ?? '';
            $this->severity = $finding->severity;
            $this->status = $finding->status;
            $this->loadClientData();
        } elseif (request()->integer('report_id')) {
            // Pre-fill from URL query param: /findings/create?report_id=X
            $report = VisitReport::with('client')->find(request()->integer('report_id'));
            if ($report) {
                $this->visit_report_id = $report->id;
                $this->client_id = $report->client_id;
                $this->loadClientData();
            }
        }
    }

    public function updatedClientId(): void
    {
        $this->asset_id = 0;
        $this->visit_report_id = 0;
        $this->loadClientData();
    }

    private function loadClientData(): void
    {
        if (!$this->client_id) {
            $this->clientAssets = [];
            $this->clientReports = [];
            return;
        }
        $this->clientAssets = Asset::where('client_id', $this->client_id)
            ->orderBy('asset_name')->get()->toArray();
        $this->clientReports = VisitReport::where('client_id', $this->client_id)
            ->with('schedule')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'label' => $r->report_number . ' — ' . $r->schedule->visit_date->format('d M Y'),
            ])->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'asset_id' => 'nullable|exists:assets,id',
            'visit_report_id' => 'nullable|exists:visit_reports,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,monitoring,resolved',
        ]);

        $data = [
            'client_id' => $this->client_id,
            'asset_id' => $this->asset_id ?: null,
            'visit_report_id' => $this->visit_report_id ?: null,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'category' => $this->category ?: null,
            'severity' => $this->severity,
            'status' => $this->status,
            'reported_by' => auth()->id(),
        ];

        if ($this->status === 'resolved' && $this->finding && $this->finding->status !== 'resolved') {
            $data['resolved_at'] = now();
        }

        if ($this->finding && $this->finding->exists) {
            $this->finding->update($data);
        } else {
            Finding::create($data);
        }

        Client::find($this->client_id)?->recalculateHealthScore();

        $redirectReport = $this->visit_report_id;
        session()->flash('success', 'Finding saved.');

        if ($redirectReport) {
            $this->redirect(route('reports.show', $redirectReport));
        } else {
            $this->redirect(route('findings.index'));
        }
    }

    public function render()
    {
        $isEdit = $this->finding && $this->finding->exists;
        $clients = Client::where('is_active', true)->orderBy('company_name')->get();

        return view('livewire.findings.finding-form', compact('isEdit', 'clients'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Finding' : 'Tambah Finding']);
    }
}
