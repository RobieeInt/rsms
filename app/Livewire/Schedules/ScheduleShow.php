<?php

namespace App\Livewire\Schedules;

use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ScheduleShow extends Component
{
    use WithFileUploads;

    public Schedule $schedule;
    public bool $showCheckinModal = false;
    public bool $showCheckoutModal = false;
    public ?float $lat = null;
    public ?float $lng = null;
    public $photo = null;

    public function mount(Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function checkIn(): void
    {
        $this->validate(['photo' => 'nullable|image|max:2048']);

        $data = [
            'status' => 'in_progress',
            'checked_in_at' => now(),
            'checkin_lat' => $this->lat,
            'checkin_lng' => $this->lng,
        ];

        if ($this->photo) {
            $data['checkin_photo'] = $this->photo->store('checkins', 'public');
        }

        $this->schedule->update($data);
        $this->showCheckinModal = false;
        $this->dispatch('notify', message: 'Checked in successfully!', type: 'success');
        $this->schedule->refresh();
    }

    public function checkOut(): void
    {
        $this->validate(['photo' => 'nullable|image|max:2048']);

        $data = [
            'status' => 'completed',
            'checked_out_at' => now(),
            'checkout_lat' => $this->lat,
            'checkout_lng' => $this->lng,
        ];

        if ($this->photo) {
            $data['checkout_photo'] = $this->photo->store('checkouts', 'public');
        }

        $this->schedule->update($data);
        $this->showCheckoutModal = false;
        $this->dispatch('notify', message: 'Checked out successfully!', type: 'success');
        $this->schedule->refresh();
    }

    public function cancel(): void
    {
        $this->schedule->update(['status' => 'cancelled']);
        $this->dispatch('notify', message: 'Schedule cancelled.', type: 'warning');
        $this->schedule->refresh();
    }

    public function render()
    {
        $this->schedule->load(['client', 'technician', 'visitReport']);

        return view('livewire.schedules.schedule-show')
            ->layout('layouts.app', ['title' => 'Schedule Detail']);
    }
}
