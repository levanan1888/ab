<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.list-projects';

    #[Url]
    public string $project_status = 'active';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo nhóm'),
        ];
    }

    public function getTitle(): string
    {
        return 'Nhóm làm việc';
    }

    public function getProjectCards(): Collection
    {
        $query = ProjectResource::getEloquentQuery()
            ->with(['owner', 'members'])
            ->withCount([
                'members',
                'tasks',
                'tasks as open_tasks_count' => function ($query): void {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS, Task::STATUS_CODE_FINISH, Task::STATUS_CODE_REVIEW, Task::STATUS_REVIEW_DONE, Task::STATUS_TEST_READY, Task::STATUS_TESTING, Task::STATUS_REOPEN]);
                },
                'tasks as closed_tasks_count' => function ($query): void {
                    $query->where('status', Task::STATUS_CLOSED);
                },
            ])
            ->with(['tasks' => function ($query): void {
                $query->select([
                    'id',
                    'project_id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'assignee_id',
                    'updated_at',
                ])
                    ->with('assignee:id,name')
                    ->latest('updated_at')
                    ->limit(4);
            }])
            ->orderBy('name');

        if ($this->project_status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->project_status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->get();
    }
}
