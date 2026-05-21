<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TaskStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $total_tasks = Task::query()->count();
        $todo_tasks = Task::query()->where('status', Task::STATUS_TODO)->count();
        $in_progress_tasks = Task::query()->where('status', Task::STATUS_IN_PROGRESS)->count();
        $done_tasks = Task::query()->where('status', Task::STATUS_DONE)->count();

        return [
            Card::make('Tổng công việc', (string) $total_tasks),
            Card::make('Chưa làm', (string) $todo_tasks),
            Card::make('Đang làm', (string) $in_progress_tasks),
            Card::make('Hoàn thành', (string) $done_tasks),
        ];
    }
}
