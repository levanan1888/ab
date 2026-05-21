<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->members()->syncWithoutDetaching([
            Auth::id() => ['role_in_project' => 'leader'],
        ]);
    }
}
