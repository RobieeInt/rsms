<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;

class UserProfile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $position = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public $avatar = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->position = $user->position ?? '';
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }

        auth()->user()->update($data);
        $this->dispatch('notify', message: 'Profile updated.', type: 'success');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!password_verify($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        auth()->user()->update(['password' => bcrypt($this->new_password)]);
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->dispatch('notify', message: 'Password updated.', type: 'success');
    }

    public function render()
    {
        return view('livewire.profile.user-profile')
            ->layout('layouts.app', ['title' => 'My Profile']);
    }
}
