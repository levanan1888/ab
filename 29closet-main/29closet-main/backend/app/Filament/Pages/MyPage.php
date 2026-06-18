<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
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

        if ($user === null) {
            return collect();
        }

        return $this->getMyTasksQuery($user->id)
            ->with('project:id,name')
            ->orderByRaw("case when priority = 'high' then 0 when priority = 'medium' then 1 else 2 end")
            ->orderByRaw("case when status = 'in_progress' then 0 else 1 end")
            ->orderBy('deadline')
            ->get();
    }

    public function getDelegatedTasks(): Collection
    {
        $user = Auth::user();

        if ($user === null || ! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_LEADER], true)) {
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
        $user = Auth::user();

        if ($user === null) {
            return [];
        }

        return [
            [
                'key' => Task::STATUS_NEW,
                'label' => 'Mới',
                'count' => $this->getMyTasksQuery($user->id)
                    ->whereIn('status', $this->getTodoStatuses())
                    ->count(),
                'color' => '#475569',
                'background' => '#f8fafc',
                'border' => '#cbd5e1',
            ],
            [
                'key' => Task::STATUS_IN_PROGRESS,
                'label' => 'Đang làm',
                'count' => $this->getMyTasksQuery($user->id)
                    ->whereIn('status', $this->getInProgressStatuses())
                    ->count(),
                'color' => '#b45309',
                'background' => '#fff7ed',
                'border' => '#fdba74',
            ],
            [
                'key' => Task::STATUS_CLOSED,
                'label' => 'Hoàn thành',
                'count' => $this->getMyTasksQuery($user->id)
                    ->whereIn('status', $this->getCompletedStatuses())
                    ->count(),
                'color' => '#047857',
                'background' => '#ecfdf5',
                'border' => '#6ee7b7',
            ],
        ];
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            Task::STATUS_NEW => 'Mới',
            Task::STATUS_PENDING => 'Chờ xử lý',
            Task::STATUS_REOPEN => 'Mở lại',
            Task::STATUS_IN_PROGRESS => 'Đang làm',
            Task::STATUS_CODE_FINISH => 'Code xong',
            Task::STATUS_CODE_REVIEW => 'Code review',
            Task::STATUS_REVIEW_DONE => 'Review xong',
            Task::STATUS_TEST_READY => 'Sẵn sàng test',
            Task::STATUS_TESTING => 'Đang test',
            Task::STATUS_TEST_DONE => 'Test xong',
            Task::STATUS_DONE, Task::STATUS_CLOSED => 'Hoàn thành',
            default => $status,
        };
    }

    public function getStatusBadgeStyles(string $status): string
    {
        if (in_array($status, $this->getCompletedStatuses(), true)) {
            return 'background:#dcfce7;color:#166534;border:1px solid #22c55e;font-weight:700;';
        }

        if (in_array($status, $this->getInProgressStatuses(), true)) {
            return 'background:#ffedd5;color:#c2410c;border:1px solid #f97316;font-weight:700;';
        }

        return 'background:#e2e8f0;color:#334155;border:1px solid #94a3b8;font-weight:700;';
    }

    public function getPriorityLabel(string $priority): string
    {
        return match ($priority) {
            Task::PRIORITY_HIGH => 'GẤP',
            Task::PRIORITY_MEDIUM => 'Ưu tiên',
            Task::PRIORITY_LOW => 'Bình thường',
            default => $priority,
        };
    }

    public function getPriorityBadgeStyles(string $priority): string
    {
        return match ($priority) {
            Task::PRIORITY_HIGH => 'background:#dc2626;color:#ffffff;border:2px solid #991b1b;font-weight:800;box-shadow:0 0 0 2px rgba(220,38,38,0.18);',
            Task::PRIORITY_MEDIUM => 'background:#f59e0b;color:#ffffff;border:2px solid #b45309;font-weight:800;box-shadow:0 0 0 2px rgba(245,158,11,0.16);',
            Task::PRIORITY_LOW => 'background:#16a34a;color:#ffffff;border:2px solid #166534;font-weight:800;',
            default => 'background:#64748b;color:#ffffff;border:2px solid #475569;font-weight:700;',
        };
    }

    public function getTaskRowStyles(Task $task): string
    {
        return match ($task->priority) {
            Task::PRIORITY_HIGH => 'background:#fff1f2;border-left:8px solid #dc2626;',
            Task::PRIORITY_MEDIUM => 'background:#fffbeb;border-left:8px solid #f59e0b;',
            Task::PRIORITY_LOW => 'background:#f0fdf4;border-left:8px solid #16a34a;',
            default => 'background:#ffffff;border-left:8px solid #cbd5e1;',
        };
    }

    public function getDeadlineStyles(Task $task): string
    {
        if ($task->deadline === null) {
            return 'color:#64748b;';
        }

        if ($task->deadline->isPast() && ! in_array($task->status, $this->getCompletedStatuses(), true)) {
            return 'color:#dc2626;font-weight:800;';
        }

        if ($task->priority === Task::PRIORITY_HIGH) {
            return 'color:#991b1b;font-weight:700;';
        }

        if ($task->priority === Task::PRIORITY_MEDIUM) {
            return 'color:#b45309;font-weight:700;';
        }

        return 'color:#166534;font-weight:600;';
    }

    public function getPriorityFilterOptions(): array
    {
        return [
            '' => 'Tất cả ưu tiên',
            Task::PRIORITY_HIGH => 'Gấp',
            Task::PRIORITY_MEDIUM => 'Ưu tiên',
            Task::PRIORITY_LOW => 'Bình thường',
        ];
    }

    public function getStatusFilterOptions(): array
    {
        return [
            '' => 'Tất cả trạng thái',
            'todo' => 'Mới / Chờ xử lý',
            'in_progress' => 'Đang làm',
            'completed' => 'Hoàn thành',
            Task::STATUS_TESTING => 'Đang test',
            Task::STATUS_CODE_REVIEW => 'Code review',
            Task::STATUS_PENDING => 'Chờ xử lý',
        ];
    }

    public function getDeadlineFilterOptions(): array
    {
        return [
            '' => 'Tất cả hạn chót',
            'overdue' => 'Quá hạn',
            'today' => 'Hôm nay',
            'week' => '7 ngày tới',
            'none' => 'Chưa có hạn',
        ];
    }

    public function getSelectedPriority(): string
    {
        return (string) request()->query('priority', '');
    }

    public function getSelectedStatus(): string
    {
        return (string) request()->query('status_filter', '');
    }

    public function getSelectedDeadline(): string
    {
        return (string) request()->query('deadline_filter', '');
    }

    public function getResetFilterUrl(): string
    {
        return static::getUrl();
    }

    private function getMyTasksQuery(int $userId): Builder
    {
        $query = Task::query()
            ->where(function (Builder $query) use ($userId): void {
                $query
                    ->where('assignee_id', $userId)
                    ->orWhereHas('assignees', function (Builder $assigneeQuery) use ($userId): void {
                        $assigneeQuery->where('users.id', $userId);
                    });
            })
            ->whereIn('status', [
                ...$this->getTodoStatuses(),
                ...$this->getInProgressStatuses(),
                ...$this->getCompletedStatuses(),
            ]);

        $priority = $this->getSelectedPriority();
        if ($priority !== '') {
            $query->where('priority', $priority);
        }

        $statusFilter = $this->getSelectedStatus();
        if ($statusFilter !== '') {
            match ($statusFilter) {
                'todo' => $query->whereIn('status', $this->getTodoStatuses()),
                'in_progress' => $query->whereIn('status', $this->getInProgressStatuses()),
                'completed' => $query->whereIn('status', $this->getCompletedStatuses()),
                default => $query->where('status', $statusFilter),
            };
        }

        $deadlineFilter = $this->getSelectedDeadline();
        if ($deadlineFilter !== '') {
            match ($deadlineFilter) {
                'overdue' => $query
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
                    ->whereNotIn('status', $this->getCompletedStatuses()),
                'today' => $query->whereDate('deadline', now()->toDateString()),
                'week' => $query
                    ->whereNotNull('deadline')
                    ->whereBetween('deadline', [now()->startOfDay(), now()->copy()->addDays(7)->endOfDay()]),
                'none' => $query->whereNull('deadline'),
                default => null,
            };
        }

        return $query;
    }

    private function getTodoStatuses(): array
    {
        return [
            Task::STATUS_NEW,
            Task::STATUS_PENDING,
            Task::STATUS_REOPEN,
        ];
    }

    private function getInProgressStatuses(): array
    {
        return [
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_CODE_FINISH,
            Task::STATUS_CODE_REVIEW,
            Task::STATUS_REVIEW_DONE,
            Task::STATUS_TEST_READY,
            Task::STATUS_TESTING,
        ];
    }

    private function getCompletedStatuses(): array
    {
        return [
            Task::STATUS_TEST_DONE,
            Task::STATUS_DONE,
            Task::STATUS_CLOSED,
        ];
    }
}
