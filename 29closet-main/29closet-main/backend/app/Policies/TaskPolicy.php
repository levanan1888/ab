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
        return $task->project->isMember($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function update(User $user, Task $task): bool
    {
        if ($task->project->isProjectLeader($user)) {
            return true;
        }

        return $task->assignee_id === $user->id && $task->project->isMember($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->project->isProjectLeader($user);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }
}
