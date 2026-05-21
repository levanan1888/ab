<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Task;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.edit-project';

    public function getTitle(): string
    {
        return 'Tổng quan dự án';
    }

    public function getOverviewRows(): array
    {
        return [
            'Tất cả công việc' => $this->getTaskCounts(),
            'Ưu tiên cao' => $this->getTaskCounts(Task::PRIORITY_HIGH),
            'Ưu tiên trung bình' => $this->getTaskCounts(Task::PRIORITY_MEDIUM),
            'Ưu tiên thấp' => $this->getTaskCounts(Task::PRIORITY_LOW),
        ];
    }

    public function getTaskCounts(?string $priority = null): array
    {
        $query = $this->record->tasks();

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        $open_count = (clone $query)->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])->count();
        $closed_count = (clone $query)->where('status', Task::STATUS_DONE)->count();

        return [
            'open' => $open_count,
            'closed' => $closed_count,
            'total' => $open_count + $closed_count,
        ];
    }

    public function getRecentTasks()
    {
        return $this->record->tasks()
            ->with('assignee:id,name')
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
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
