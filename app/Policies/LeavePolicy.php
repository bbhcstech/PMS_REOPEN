<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    public function view(User $user, Leave $leave): bool
    {
        return $user->role === 'admin' || $leave->user_id === $user->id;
    }

    public function update(User $user, Leave $leave): bool
    {
        return $user->role === 'admin' || ($leave->user_id === $user->id && $leave->status === 'pending');
    }

    public function approve(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Leave $leave): bool
    {
        return $user->role === 'admin' || ($leave->user_id === $user->id && $leave->status === 'pending');
    }
}
