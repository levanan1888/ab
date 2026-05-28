<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class TaskKanbanBoard extends KanbanBoard
{
    protected static string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Bảng Kanban';

    protected static ?string $title = 'Bảng Kanban';

    protected static ?string $navigationGroup = 'Công việc';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $recordTitleAttribute = 'kanban_title';
    #[Url]
    public ?int $project_id = null;

    protected function statuses(): Collection
    {
        return collect([
            ['id' => Task::STATUS_NEW, 'title' => 'New'],
            ['id' => Task::STATUS_PENDING, 'title' => 'Pending'],
            ['id' => Task::STATUS_IN_PROGRESS, 'title' => 'Đang làm'],
            ['id' => Task::STATUS_CODE_FINISH, 'title' => 'Code finish'],
            ['id' => Task::STATUS_CODE_REVIEW, 'title' => 'Code review'],
            ['id' => Task::STATUS_REVIEW_DONE, 'title' => 'Review done'],
            ['id' => Task::STATUS_TEST_READY, 'title' => 'Test ready'],
            ['id' => Task::STATUS_TESTING, 'title' => 'Testing'],
            ['id' => Task::STATUS_TEST_DONE, 'title' => 'Test done'],
            ['id' => Task::STATUS_REJECTED, 'title' => 'Rejected'],
            ['id' => Task::STATUS_REOPEN, 'title' => 'Reopen'],
            ['id' => Task::STATUS_CLOSED, 'title' => 'Closed'],
        ]);
    }

    protected function records(): Collection
    {
        $query = Task::query()->with(['assignee:id,name', 'assignees:id,name']);
        $user = Filament::auth()->user();

        if ($this->project_id !== null) {
            $query->where('project_id', $this->project_id);
        }

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $query->whereHas('project.members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        return $query->get();
    }

    public function onStatusChanged($record_id, $status, array $from, array $to): void
    {
        $task = Task::query()->findOrFail($record_id);
        $task->status = $status;
        $task->completed_at = in_array($status, [Task::STATUS_DONE, Task::STATUS_CLOSED, Task::STATUS_TEST_DONE], true) ? now() : null;
        $task->save();

        $user = Filament::auth()->user();

        if ($user !== null) {
            ActivityLog::query()->create([
                'subject_type' => 'task',
                'subject_id' => $task->id,
                'action' => 'status_changed',
                'causer_id' => $user->id,
                'meta' => ['status' => $status, 'task_title' => $task->title],
            ]);
        }
    }
}
