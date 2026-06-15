<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;
    protected static string $view = 'filament.resources.task-resource.pages.list-tasks';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
