<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function view(User $user, TaskComment $task_comment): bool
    {
        if ($user->is_leader()) {
            return true;
        }

        return $task_comment->task->project->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function update(User $user, TaskComment $task_comment): bool
    {
        return $user->is_leader() || $task_comment->user_id === $user->id;
    }

    public function delete(User $user, TaskComment $task_comment): bool
    {
        return $user->is_leader() || $task_comment->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_leader();
    }
}
