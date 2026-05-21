<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProjectProgressTable extends BaseWidget
{
    protected static ?string $heading = 'Tiến độ dự án';

    public function table(Table $table): Table
    {
        return $table
            ->query(Project::query()->withCount('tasks'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Dự án')->searchable(),
                Tables\Columns\TextColumn::make('tasks_count')->label('Tổng công việc'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ]);
    }
}
