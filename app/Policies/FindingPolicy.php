<?php

namespace App\Policies;

use App\Models\Finding;
use App\Models\User;

class FindingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function view(User $user, Finding $finding): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $finding->reported_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function update(User $user, Finding $finding): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $finding->reported_by === $user->id;
    }

    public function delete(User $user, Finding $finding): bool
    {
        return $user->hasRole('admin');
    }
}
