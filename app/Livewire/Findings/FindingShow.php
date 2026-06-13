<?php

namespace App\Livewire\Findings;

use App\Models\Finding;
use App\Models\Recommendation;
use Livewire\Component;

class FindingShow extends Component
{
    public Finding $finding;
    public string $recommendation = '';
    public string $priority = 'medium';

    public function mount(Finding $finding): void
    {
        $this->finding = $finding;
    }

    public function addRecommendation(): void
    {
        $this->validate([
            'recommendation' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        Recommendation::create([
            'finding_id' => $this->finding->id,
            'created_by' => auth()->id(),
            'recommendation' => $this->recommendation,
            'priority' => $this->priority,
        ]);

        $this->recommendation = '';
        $this->priority = 'medium';
        $this->dispatch('notify', message: 'Recommendation added.', type: 'success');
    }

    public function updateStatus(string $status): void
    {
        $data = ['status' => $status];
        if ($status === 'resolved') {
            $data['resolved_at'] = now();
        }
        $this->finding->update($data);
        $this->finding->client->recalculateHealthScore();
        $this->finding->refresh();
        $this->dispatch('notify', message: 'Status updated.', type: 'success');
    }

    public function render()
    {
        $this->finding->load(['client', 'asset', 'reporter', 'recommendations.creator']);

        return view('livewire.findings.finding-show')
            ->layout('layouts.app', ['title' => 'Finding Detail']);
    }
}
