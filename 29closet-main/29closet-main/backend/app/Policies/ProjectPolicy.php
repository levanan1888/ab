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
        return $project->isMember($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }

    public function update(User $user, Project $project): bool
    {
        return $project->isProjectLeader($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->isProjectLeader($user);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_LEADER, User::ROLE_MEMBER], true);
    }
}
