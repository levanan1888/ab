<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Pages\TaskKanbanBoard;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TaskResource;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.edit-project';

    public function getTitle(): string
    {
        return (string) ($this->record->name ?? 'Tổng quan nhóm');
    }

    public function getOverviewRows(): array
    {
        return [
            'Tất cả công việc' => ['priority' => null] + $this->getTaskCounts(),
            'Ưu tiên cao' => ['priority' => Task::PRIORITY_HIGH] + $this->getTaskCounts(Task::PRIORITY_HIGH),
            'Ưu tiên trung bình' => ['priority' => Task::PRIORITY_MEDIUM] + $this->getTaskCounts(Task::PRIORITY_MEDIUM),
            'Ưu tiên thấp' => ['priority' => Task::PRIORITY_LOW] + $this->getTaskCounts(Task::PRIORITY_LOW),
        ];
    }

    public function getTaskCounts(?string $priority = null): array
    {
        $query = $this->baseTasksQuery();

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        $open_count = (clone $query)->whereIn('status', [
            Task::STATUS_TODO,
            Task::STATUS_NEW,
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_CODE_FINISH,
            Task::STATUS_CODE_REVIEW,
            Task::STATUS_REVIEW_DONE,
            Task::STATUS_TEST_READY,
            Task::STATUS_TESTING,
            Task::STATUS_REOPEN,
            Task::STATUS_WAITING_REJECT,
            Task::STATUS_REJECTED,
        ])->count();
        $closed_count = (clone $query)->whereIn('status', [
            Task::STATUS_TEST_DONE,
            Task::STATUS_DONE,
            Task::STATUS_CLOSED,
        ])->count();

        return [
            'open' => $open_count,
            'closed' => $closed_count,
            'total' => $open_count + $closed_count,
        ];
    }

    public function getRecentTasks()
    {
        return $this->baseTasksQuery()
            ->with('assignee:id,name')
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    public function getTaskListUrl(?string $priority = null, ?string $status = null): string
    {
        $table_filters = [
            'project_id' => ['value' => $this->record->id],
        ];

        if ($priority !== null) {
            $table_filters['priority'] = ['value' => $priority];
        }

        if ($status !== null) {
            $table_filters['status'] = ['value' => $status];
        }

        return TaskResource::getUrl('index', ['tableFilters' => $table_filters]);
    }

    public function getProjectTabUrls(): array
    {
        $task_filters = [
            'project_id' => ['value' => $this->record->id],
        ];

        return [
            'overview' => static::getResource()::getUrl('edit', ['record' => $this->record]),
            'activity' => static::getResource()::getUrl('edit', ['record' => $this->record, 'tab' => 'activity']),
            'issues' => TaskResource::getUrl('index', ['tableFilters' => $task_filters]),
            'dashboard' => TaskKanbanBoard::getUrl(['project_id' => $this->record->id]),
            'gantt' => static::getResource()::getUrl('gantt', ['record' => $this->record]),
        ];
    }

    public function getActivities(): Collection
    {
        return ActivityLog::query()
            ->where('subject_type', 'task')
            ->whereIn('subject_id', $this->record->tasks()->pluck('id'))
            ->with('causer:id,name')
            ->latest()
            ->limit(100)
            ->get();
    }

    protected function baseTasksQuery()
    {
        // Overview should reflect the whole team's work; member-specific filtering
        // is applied when navigating to the task list via getTaskListUrl().
        return $this->record->tasks();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Giải tán nhóm')
                ->modalHeading('Giải tán nhóm')
                ->modalDescription('Bạn có chắc chắn muốn giải tán nhóm này? Toàn bộ dữ liệu liên quan có thể bị xóa.')
                ->modalSubmitActionLabel('Giải tán nhóm')
                ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
        ];
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    protected function getFormActions(): array
    {
        if (Auth::user()?->role === User::ROLE_MEMBER) {
            return [];
        }

        return parent::getFormActions();
    }
}
