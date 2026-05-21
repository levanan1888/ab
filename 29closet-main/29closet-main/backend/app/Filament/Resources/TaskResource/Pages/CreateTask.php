<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        if (($data['status'] ?? null) === 'done') {
            $data['completed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => 'created',
            'causer_id' => Auth::id(),
            'meta' => ['status' => $this->record->status],
        ]);
    }
}
