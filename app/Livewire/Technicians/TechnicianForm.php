<?php

namespace App\Livewire\Technicians;

use App\Models\User;
use Livewire\Component;

class TechnicianForm extends Component
{
    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';
    public string $position = '';
    public bool $is_active = true;

    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->position = $user->position ?? '';
            $this->is_active = $user->is_active;
        }
    }

    public function save(): void
    {
        $isEdit = $this->user && $this->user->exists;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($isEdit ? ",{$this->user->id}" : ''),
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];

        if (!$isEdit) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($isEdit) {
            $this->user->update($data);
        } else {
            $user = User::create($data);
            $user->assignRole('technician');
        }

        session()->flash('success', 'Technician saved successfully.');
        $this->redirect(route('technicians.index'));
    }

    public function render()
    {
        $isEdit = $this->user && $this->user->exists;
        return view('livewire.technicians.technician-form', compact('isEdit'))
            ->layout('layouts.app', ['title' => $isEdit ? 'Edit Technician' : 'Add Technician']);
    }
}
