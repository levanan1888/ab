<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class MyPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Page';

    protected static ?string $title = 'My Page';

    protected static ?string $navigationGroup = 'Công việc';

    protected static string $view = 'filament.pages.my-page';

    public function getPageHeading(): string
    {
        return 'My Page';
    }

    public function getMyTasks(): Collection
    {
        $user = Auth::user();
        $query = Task::query();

        $query->where('assignee_id', Auth::id());

        return $query
            ->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_CODE_FINISH, Task::STATUS_CODE_REVIEW, Task::STATUS_REVIEW_DONE, Task::STATUS_TEST_READY, Task::STATUS_TESTING, Task::STATUS_REOPEN])
            ->with('project:id,name')
            ->orderByRaw("case when status = 'in_progress' then 0 else 1 end")
            ->orderBy('deadline')
            ->get();
    }

    public function getDelegatedTasks(): Collection
    {
        $user = Auth::user();

        if ($user === null || $user->role !== User::ROLE_LEADER) {
            return collect();
        }

        return Task::query()
            ->where('creator_id', $user->id)
            ->where('assignee_id', '!=', $user->id)
            ->with(['project:id,name', 'assignee:id,name'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();
    }

    public function getStatusSummary(): array
    {
        return [
            [
                'key' => Task::STATUS_NEW,
                'label' => 'Mới',
                'count' => Task::query()->where('status', Task::STATUS_NEW)->count(),
                'color' => '#6b7280',
            ],
            [
                'key' => Task::STATUS_IN_PROGRESS,
                'label' => 'Đang làm',
                'count' => Task::query()->where('status', Task::STATUS_IN_PROGRESS)->count(),
                'color' => '#d97706',
            ],
            [
                'key' => Task::STATUS_CLOSED,
                'label' => 'Hoàn thành',
                'count' => Task::query()->where('status', Task::STATUS_CLOSED)->count(),
                'color' => '#059669',
            ],
        ];
    }

    public function getStatusLabel(string $status): string
    {
        if ($status === Task::STATUS_NEW) {
            return 'New';
        }

        if ($status === Task::STATUS_IN_PROGRESS) {
            return 'Đang làm';
        }

        if ($status === Task::STATUS_CLOSED) {
            return 'Hoàn thành';
        }

        return $status;
    }
}
