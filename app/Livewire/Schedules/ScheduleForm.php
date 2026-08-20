<?php

namespace App\Livewire\Schedules;

use App\Models\Client;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\ScheduleCreatedNotification;
use App\Notifications\ScheduleUpdatedNotification;
use App\Notifications\TechnicianScheduleNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

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
            $previousTechnicianId = $this->schedule->technician_id;

            $this->schedule->update($data);
            $this->schedule->load(['client', 'technician']);

            // Email ke klien saat jadwal diperbarui — best-effort: SMTP outage
            // must not make an otherwise-successful reschedule look like it failed.
            if ($this->schedule->client->pic_email) {
                try {
                    $this->schedule->client->notifyNow(new ScheduleUpdatedNotification($this->schedule));
                    $this->schedule->logSend('updated', $this->schedule->client->pic_email);
                } catch (Throwable $e) {
                    $this->schedule->logSend('updated', $this->schedule->client->pic_email, 'failed', $e->getMessage());
                    Log::warning('Gagal mengirim notifikasi jadwal diperbarui ke klien: '.$e->getMessage());
                }
            }

            // Reassignment: both the outgoing and incoming technician need
            // to know — neither previously got notified of this at all.
            if ($previousTechnicianId !== $this->schedule->technician_id) {
                try {
                    $previousTechnician = User::find($previousTechnicianId);
                    $previousTechnician?->notifyNow(new TechnicianScheduleNotification($this->schedule, 'cancelled'));
                    $this->schedule->technician->notifyNow(new TechnicianScheduleNotification($this->schedule, 'reassigned'));
                } catch (Throwable $e) {
                    Log::warning('Gagal mengirim notifikasi reassignment ke teknisi: '.$e->getMessage());
                }
            }
        } else {
            $schedule = Schedule::create($data);
            $schedule->load(['client', 'technician']);

            // Email ke teknisi
            try {
                $schedule->technician->notifyNow(new TechnicianScheduleNotification($schedule, 'created'));
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim notifikasi jadwal baru ke teknisi: '.$e->getMessage());
            }

            // Email ke klien — best-effort: SMTP outage must not make an
            // otherwise-successful schedule creation look like it failed.
            if ($schedule->client->pic_email) {
                try {
                    $schedule->client->notifyNow(new ScheduleCreatedNotification($schedule));
                    $schedule->logSend('created', $schedule->client->pic_email);
                } catch (Throwable $e) {
                    $schedule->logSend('created', $schedule->client->pic_email, 'failed', $e->getMessage());
                    Log::warning('Gagal mengirim notifikasi jadwal baru ke klien: '.$e->getMessage());
                }
            }
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
