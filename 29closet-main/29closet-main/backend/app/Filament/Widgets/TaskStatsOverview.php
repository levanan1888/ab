<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $user = Auth::user();
        $query = Task::query();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $query->whereHas('project.members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        $total_tasks = (clone $query)->count();
        $todo_tasks = (clone $query)->where('status', Task::STATUS_NEW)->count();
        $in_progress_tasks = (clone $query)->where('status', Task::STATUS_IN_PROGRESS)->count();
        $done_tasks = (clone $query)->where('status', Task::STATUS_CLOSED)->count();
        $my_open_tasks = Auth::id() ? (clone $query)->where('assignee_id', Auth::id())->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_CODE_FINISH, Task::STATUS_CODE_REVIEW, Task::STATUS_REVIEW_DONE, Task::STATUS_TEST_READY, Task::STATUS_TESTING, Task::STATUS_REOPEN])->count() : 0;
        $my_done_tasks = Auth::id() ? (clone $query)->where('assignee_id', Auth::id())->where('status', Task::STATUS_CLOSED)->count() : 0;
        $my_overdue_tasks = Auth::id() ? (clone $query)->where('assignee_id', Auth::id())->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_CODE_FINISH, Task::STATUS_CODE_REVIEW, Task::STATUS_REVIEW_DONE, Task::STATUS_TEST_READY, Task::STATUS_TESTING, Task::STATUS_REOPEN])->whereNotNull('deadline')->where('deadline', '<', now())->count() : 0;

        return [
            Card::make('Tổng công việc', (string) $total_tasks),
            Card::make('Chưa làm', (string) $todo_tasks),
            Card::make('Đang làm', (string) $in_progress_tasks),
            Card::make('Hoàn thành', (string) $done_tasks),
            Card::make('Việc của tôi (mở)', (string) $my_open_tasks),
            Card::make('Việc của tôi (hoàn thành)', (string) $my_done_tasks),
            Card::make('Việc của tôi (quá hạn)', (string) $my_overdue_tasks),
        ];
    }
}
