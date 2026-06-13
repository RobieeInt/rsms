<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $schedule->technician_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $schedule->technician_id === $user->id;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('admin');
    }
}
