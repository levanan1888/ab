<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->is_leader()) {
            return true;
        }

        return $task->assignee_id === $user->id || $task->project->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_leader();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->is_leader()) {
            return true;
        }

        return $task->assignee_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->is_leader();
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_leader();
    }
}
