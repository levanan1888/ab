<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Task;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class TaskKanbanBoard extends KanbanBoard
{
    protected static string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Bảng Kanban';

    protected static ?string $title = 'Bảng Kanban';

    protected static ?string $navigationGroup = 'Công việc';

    protected static string $recordTitleAttribute = 'title';

    protected function statuses(): Collection
    {
        return collect([
            ['id' => Task::STATUS_TODO, 'title' => 'Chưa làm'],
            ['id' => Task::STATUS_IN_PROGRESS, 'title' => 'Đang làm'],
            ['id' => Task::STATUS_DONE, 'title' => 'Hoàn thành'],
        ]);
    }

    public function onStatusChanged($record_id, $status, array $from, array $to): void
    {
        $task = Task::query()->findOrFail($record_id);
        $task->status = $status;
        $task->completed_at = $status === Task::STATUS_DONE ? now() : null;
        $task->save();

        $user = Filament::auth()->user();

        if ($user !== null) {
            ActivityLog::query()->create([
                'subject_type' => 'task',
                'subject_id' => $task->id,
                'action' => 'status_changed',
                'causer_id' => $user->id,
                'meta' => ['status' => $status],
            ]);
        }
    }
}
