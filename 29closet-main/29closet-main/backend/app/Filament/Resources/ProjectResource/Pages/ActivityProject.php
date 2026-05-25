<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Collection;

class ActivityProject extends ProjectTabPage
{
    protected static string $view = 'filament.resources.project-resource.pages.tabs.activity-project';

    public function getActivities(): Collection
    {
        return ActivityLog::query()
            ->where('subject_type', 'task')
            ->whereIn('subject_id', $this->record->tasks()->pluck('id'))
            ->with('causer:id,name')
            ->latest()
            ->limit(100)
            ->get();
    }
}
