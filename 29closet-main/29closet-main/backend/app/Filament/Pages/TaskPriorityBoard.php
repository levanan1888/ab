<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskPriorityBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static ?string $navigationLabel = 'Bảng ưu tiên';

    protected static ?string $title = 'Bảng ưu tiên kéo thả';

    protected static ?string $navigationGroup = 'Công việc';

    protected static string $view = 'filament.pages.task-priority-board';

    public function getTasksByPriority(): array
    {
        $query = Task::query()->with(['project:id,name', 'assignee:id,name', 'assignees:id,name']);
        $user = Filament::auth()->user();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $query->whereHas('project.members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        $tasks = $query->orderByDesc('updated_at')->get();

        return [
            Task::PRIORITY_HIGH => $tasks->where('priority', Task::PRIORITY_HIGH)->values()->all(),
            Task::PRIORITY_MEDIUM => $tasks->where('priority', Task::PRIORITY_MEDIUM)->values()->all(),
            Task::PRIORITY_LOW => $tasks->where('priority', Task::PRIORITY_LOW)->values()->all(),
        ];
    }

    public function updateTaskPriority(int $taskId, string $priority): void
    {
        if (! in_array($priority, [Task::PRIORITY_LOW, Task::PRIORITY_MEDIUM, Task::PRIORITY_HIGH], true)) {
            return;
        }

        $task = Task::query()->findOrFail($taskId);
        $user = Filament::auth()->user();

        if ($user !== null && $user->role === User::ROLE_MEMBER && ! $task->project->isMember($user)) {
            abort(403);
        }

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $isAssigned = $task->assignee_id === $user->id || $task->assignees()->where('users.id', $user->id)->exists();
            if (! $isAssigned) {
                Notification::make()->title('Bạn chỉ được kéo-thả task được giao cho mình')->danger()->send();
                return;
            }
        }

        $task->priority = $priority;
        $task->save();

        Notification::make()->title('Đã cập nhật mức ưu tiên')->success()->send();
    }
}
