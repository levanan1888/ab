<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ActivityLog;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
    protected array $assigneeIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = now();
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $state = $this->form->getState();
        $this->assigneeIds = array_values(array_unique(array_filter((array) ($state['assignee_ids'] ?? []))));

        if (count($this->assigneeIds) === 0 && empty($state['assignee_id'])) {
            Notification::make()->title('Cần chọn ít nhất một người thực hiện')->danger()->send();
            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        if (count($this->assigneeIds) > 0) {
            $this->record->assignees()->sync($this->assigneeIds);
        } elseif ($this->record->assignee_id !== null) {
            $this->record->assignees()->sync([$this->record->assignee_id]);
        }

        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => 'created',
            'causer_id' => Auth::id(),
            'meta' => ['status' => $this->record->status, 'task_title' => $this->record->title],
        ]);
    }
}
