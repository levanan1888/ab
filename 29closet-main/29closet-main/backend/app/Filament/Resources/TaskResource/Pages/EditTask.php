<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            return [
                'project_id' => $this->record->project_id,
                'title' => $this->record->title,
                'description' => $this->record->description,
                'assignee_id' => $this->record->assignee_id,
                'status' => $data['status'] ?? $this->record->status,
                'priority' => $this->record->priority,
                'deadline' => $this->record->deadline,
                'creator_id' => $this->record->creator_id,
                'completed_at' => ($data['status'] ?? $this->record->status) === Task::STATUS_DONE ? now() : null,
            ];
        }

        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_DONE ? now() : null;

        return $data;
    }

    protected function afterSave(): void
    {
        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => 'updated',
            'causer_id' => Auth::id(),
            'meta' => ['status' => $this->record->status],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
