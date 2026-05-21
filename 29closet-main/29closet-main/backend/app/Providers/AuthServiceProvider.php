<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Project;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        TaskComment::class => TaskCommentPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
