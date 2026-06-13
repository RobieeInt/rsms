<?php

namespace App\Livewire\Reports;

use App\Models\Asset;
use App\Models\AssetChecklist;
use App\Models\NetworkChecklist;
use App\Models\Schedule;
use App\Models\VisitReport;
use App\Models\VisitPhoto;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReportForm extends Component
{
    use WithFileUploads;

    public Schedule $schedule;
    public ?VisitReport $report = null;

    public string $summary = '';
    public string $overall_notes = '';
    public string $client_signed_by = '';
    public string $technician_signature = '';
    public string $client_signature = '';

    public array $selectedAssetIds = [];
    public array $assetChecklists = [];
    public array $networkChecklist = [
        'internet_connectivity' => 'na', 'internet_connectivity_notes' => '',
        'speed_test' => 'na', 'speed_test_notes' => '', 'download_speed' => '', 'upload_speed' => '',
        'router_check' => 'na', 'router_check_notes' => '',
        'lan_cable_check' => 'na', 'lan_cable_check_notes' => '',
        'ip_conflict_check' => 'na', 'ip_conflict_check_notes' => '',
        'general_notes' => '',
    ];
    public array $photos = [];

    public function mount(?Schedule $schedule = null, ?VisitReport $report = null): void
    {
        // Edit route only passes {report} — get schedule from report
        if ($report && $report->exists) {
            $this->report = $report;
            $report->load(['assetChecklists', 'networkChecklist', 'schedule.client.assets']);
            $schedule = $report->schedule;
            $this->summary = $report->summary ?? '';
            $this->overall_notes = $report->overall_notes ?? '';
            $this->client_signed_by = $report->client_signed_by ?? '';
            $this->technician_signature = $report->technician_signature ?? '';
            $this->client_signature = $report->client_signature ?? '';
        }

        if (!$schedule || !$schedule->exists) {
            abort(404);
        }

        $this->schedule = $schedule;

        // Initialize asset checklists — only pre-populate data, selection controls visibility
        $assets = $schedule->client->assets;
        foreach ($assets as $asset) {
            $existing = $report ? $report->assetChecklists->where('asset_id', $asset->id)->first() : null;
            // When editing, pre-select assets that already have checklists
            if ($existing) {
                $this->selectedAssetIds[] = $asset->id;
            }
            $this->assetChecklists[$asset->id] = [
                'asset_id' => $asset->id,
                'asset_name' => $asset->asset_name,
                'storage_check' => $existing->storage_check ?? 'na',
                'storage_check_notes' => $existing->storage_check_notes ?? '',
                'ram_check' => $existing->ram_check ?? 'na',
                'ram_check_notes' => $existing->ram_check_notes ?? '',
                'temp_files_cleanup' => $existing->temp_files_cleanup ?? 'na',
                'temp_files_cleanup_notes' => $existing->temp_files_cleanup_notes ?? '',
                'ssd_health_check' => $existing->ssd_health_check ?? 'na',
                'ssd_health_check_notes' => $existing->ssd_health_check_notes ?? '',
                'windows_update_check' => $existing->windows_update_check ?? 'na',
                'windows_update_check_notes' => $existing->windows_update_check_notes ?? '',
                'driver_check' => $existing->driver_check ?? 'na',
                'driver_check_notes' => $existing->driver_check_notes ?? '',
                'virus_scan' => $existing->virus_scan ?? 'na',
                'virus_scan_notes' => $existing->virus_scan_notes ?? '',
                'printer_check' => $existing->printer_check ?? 'na',
                'printer_check_notes' => $existing->printer_check_notes ?? '',
                'hardware_cleaning' => $existing->hardware_cleaning ?? 'na',
                'hardware_cleaning_notes' => $existing->hardware_cleaning_notes ?? '',
                'general_notes' => $existing->general_notes ?? '',
            ];
        }

        if ($report && $report->networkChecklist) {
            $this->networkChecklist = $report->networkChecklist->toArray();
        }
    }

    public function saveReport(string $status = 'draft'): void
    {
        $data = [
            'schedule_id' => $this->schedule->id,
            'client_id' => $this->schedule->client_id,
            'technician_id' => $this->schedule->technician_id,
            'summary' => $this->summary,
            'overall_notes' => $this->overall_notes,
            'client_signed_by' => $this->client_signed_by,
            'technician_signature' => $this->technician_signature ?: null,
            'client_signature' => $this->client_signature ?: null,
            'status' => $status,
        ];

        if ($this->report && $this->report->exists) {
            $this->report->update($data);
            $report = $this->report;
        } else {
            $data['report_number'] = VisitReport::generateNumber();
            $report = VisitReport::create($data);
        }

        // Save asset checklists only for selected assets
        foreach ($this->assetChecklists as $assetId => $checklist) {
            if (!in_array((int) $assetId, array_map('intval', $this->selectedAssetIds))) {
                // Remove checklist if asset was deselected
                AssetChecklist::where('visit_report_id', $report->id)
                    ->where('asset_id', $assetId)
                    ->delete();
                continue;
            }
            $assetData = array_merge($checklist, ['visit_report_id' => $report->id]);
            unset($assetData['asset_name']);
            AssetChecklist::updateOrCreate(
                ['visit_report_id' => $report->id, 'asset_id' => $assetId],
                $assetData
            );
        }

        // Save network checklist
        NetworkChecklist::updateOrCreate(
            ['visit_report_id' => $report->id],
            array_merge($this->networkChecklist, ['visit_report_id' => $report->id])
        );

        // Save photos
        foreach ($this->photos as $photo) {
            $path = $photo->store('visit-photos', 'public');
            VisitPhoto::create([
                'visit_report_id' => $report->id,
                'uploaded_by' => auth()->id(),
                'file_path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'photo_type' => 'general',
            ]);
        }

        $this->photos = [];

        if ($status === 'completed') {
            $this->schedule->update(['status' => 'completed']);
        }

        $this->dispatch('notify', message: 'Report saved.', type: 'success');

        if ($status !== 'draft') {
            $this->redirect(route('reports.show', $report));
        }
    }

    public function saveSignature(string $field, string $data): void
    {
        $this->$field = $data;
    }

    public function render()
    {
        $this->schedule->load('client.assets');
        $isEdit = $this->report && $this->report->exists;
        $availableAssets = $this->schedule->client->assets;

        return view('livewire.reports.report-form', compact('isEdit', 'availableAssets'))
            ->layout('layouts.app', ['title' => 'Visit Report']);
    }
}
