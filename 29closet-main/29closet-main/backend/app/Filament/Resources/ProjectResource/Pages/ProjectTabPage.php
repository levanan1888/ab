<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Pages\TaskKanbanBoard;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TaskResource;
use App\Models\Project;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

abstract class ProjectTabPage extends Page
{
    protected static string $resource = ProjectResource::class;

    public Project $record;

    public function mount($record): void
    {
        $this->record = Project::query()->findOrFail($record);

        if (static::getResource()::canView($this->record)) {
            return;
        }

        Notification::make()
            ->title('Bạn không có quyền truy cập dự án này')
            ->danger()
            ->send();

        $this->redirect(static::getResource()::getUrl('index'));
    }

    public function getProjectTabUrls(): array
    {
        $taskFilters = [
            'project_id' => ['value' => $this->record->id],
        ];

        return [
            'overview' => static::getResource()::getUrl('edit', ['record' => $this->record]),
            'activity' => static::getResource()::getUrl('activity', ['record' => $this->record]),
            'issues' => TaskResource::getUrl('index', ['tableFilters' => $taskFilters]),
            'dashboard' => static::getResource()::getUrl('dashboard', ['record' => $this->record]),
            'gantt' => TaskKanbanBoard::getUrl(['project_id' => $this->record->id]),
        ];
    }
}
