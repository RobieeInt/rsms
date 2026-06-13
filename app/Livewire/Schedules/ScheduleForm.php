<?php

namespace App\Livewire\Schedules;

use App\Models\Client;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\ScheduleCreatedNotification;
use Livewire\Component;

class ScheduleForm extends Component
{
    public ?Schedule $schedule = null;

    public int $client_id = 0;
    public int $technician_id = 0;
    public string $visit_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $notes = '';

    public function mount(?Schedule $schedule = null): void
    {
        if ($schedule && $schedule->exists) {
            $this->schedule = $schedule;
            $this->client_id = $schedule->client_id;
            $this->technician_id = $schedule->technician_id;
            $this->visit_date = $schedule->visit_date->format('Y-m-d');
            $this->start_time = $schedule->start_time;
            $this->end_time = $schedule->end_time ?? '';
            $this->notes = $schedule->notes ?? '';
        } elseif (auth()->user()->hasRole('technician')) {
            $this->technician_id = auth()->id();
        }
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'technician_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'client_id' => $this->client_id,
            'technician_id' => $this->technician_id,
            'visit_date' => $this->visit_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->schedule && $this->schedule->exists) {
            $this->schedule->update($data);
        } else {
            $schedule = Schedule::create($data);
            // Send notifications
            $schedule->client->notify(new ScheduleCreatedNotification($schedule));
            $schedule->technician->notify(new ScheduleCreatedNotification($schedule));
        }

        session()->flash('success', 'Schedule saved successfully.');
        $this->redirect(route('schedules.index'));
    }

    public function render()
    {
        $isEdit = $this->schedule && $this->schedule->exists;
        $clients = Client::where('is_active', true)->orderBy('company_name')->get();
        $technicians = User::role('technician')->where('is_active', true)->orderBy('name')->get();

        return view('livewire.schedules.schedule-form', compact('isEdit', 'clients', 'technicians'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Schedule' : 'Create Schedule']);
    }
}
