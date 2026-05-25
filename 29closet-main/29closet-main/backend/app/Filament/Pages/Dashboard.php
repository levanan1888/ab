<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    public function getChartData(): array
    {
        $user = Auth::user();
        $query = Task::query();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $query->whereHas('project.members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        $todoStatuses = [
            Task::STATUS_NEW,
            Task::STATUS_PENDING,
            Task::STATUS_TODO,
        ];
        $inProgressStatuses = [
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_CODE_FINISH,
            Task::STATUS_CODE_REVIEW,
            Task::STATUS_REVIEW_DONE,
            Task::STATUS_TEST_READY,
            Task::STATUS_TESTING,
            Task::STATUS_REOPEN,
            Task::STATUS_WAITING_REJECT,
            Task::STATUS_REJECTED,
        ];
        $doneStatuses = [
            Task::STATUS_DONE,
            Task::STATUS_TEST_DONE,
            Task::STATUS_CLOSED,
        ];

        $statusCounts = [
            'todo' => (clone $query)->whereIn('status', $todoStatuses)->count(),
            'in_progress' => (clone $query)->whereIn('status', $inProgressStatuses)->count(),
            'done' => (clone $query)->whereIn('status', $doneStatuses)->count(),
        ];

        $priorityCounts = [
            'low' => (clone $query)->where('priority', Task::PRIORITY_LOW)->count(),
            'medium' => (clone $query)->where('priority', Task::PRIORITY_MEDIUM)->count(),
            'high' => (clone $query)->where('priority', Task::PRIORITY_HIGH)->count(),
        ];

        $projectQuery = Project::query();
        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $projectQuery->whereHas('members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        $projectNames = [];
        $projectTaskCounts = [];
        foreach ($projectQuery->withCount('tasks')->orderByDesc('tasks_count')->limit(8)->get() as $project) {
            $projectNames[] = $project->name;
            $projectTaskCounts[] = $project->tasks_count;
        }

        $timelineLabels = [];
        $timelineTodo = [];
        $timelineInProgress = [];
        $timelineDone = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = now()->startOfDay()->subDays($i);
            $dayEnd = (clone $dayStart)->endOfDay();
            $timelineLabels[] = $dayStart->format('d/m');
            $timelineTodo[] = (clone $query)->whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', $todoStatuses)->count();
            $timelineInProgress[] = (clone $query)->whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', $inProgressStatuses)->count();
            $timelineDone[] = (clone $query)->whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', $doneStatuses)->count();
        }

        return [
            'status' => $statusCounts,
            'priority' => $priorityCounts,
            'projects' => [
                'labels' => $projectNames,
                'values' => $projectTaskCounts,
            ],
            'timeline' => [
                'labels' => $timelineLabels,
                'todo' => $timelineTodo,
                'in_progress' => $timelineInProgress,
                'done' => $timelineDone,
            ],
        ];
    }
}
