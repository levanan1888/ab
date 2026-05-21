<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->is_leader()) {
            return true;
        }

        return $project->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->is_leader();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->is_leader() && $project->owner_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->is_leader() && $project->owner_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_leader();
    }
}
