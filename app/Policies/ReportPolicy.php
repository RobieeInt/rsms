<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisitReport;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function view(User $user, VisitReport $report): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $report->technician_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function update(User $user, VisitReport $report): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $report->technician_id === $user->id;
    }

    public function delete(User $user, VisitReport $report): bool
    {
        return $user->hasRole('admin');
    }
}
