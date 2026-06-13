<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public bool $unreadOnly = false;

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('notify', message: 'All notifications marked as read.', type: 'success');
    }

    public function markRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->when($this->unreadOnly, fn($q) => $q->whereNull('read_at'))
            ->paginate(20);

        return view('livewire.notifications.notification-center', compact('notifications'))
            ->layout('layouts.app', ['title' => 'Notifications']);
    }
}
