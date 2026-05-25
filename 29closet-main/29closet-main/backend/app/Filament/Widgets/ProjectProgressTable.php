<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProjectProgressTable extends BaseWidget
{
    protected static ?string $heading = 'Tiến độ nhóm';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $query = Project::query()
            ->withCount('tasks')
            ->withCount([
                'tasks as done_tasks_count' => function (Builder $builder): void {
                    $builder->where('status', Task::STATUS_DONE);
                },
            ]);

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            $query->whereHas('members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nhóm làm việc')->searchable(),
                Tables\Columns\TextColumn::make('tasks_count')->label('Tổng công việc'),
                Tables\Columns\TextColumn::make('done_tasks_count')->label('Đã hoàn thành'),
                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Tiến độ')
                    ->state(function (Project $record): string {
                        if ((int) $record->tasks_count === 0) {
                            return '0%';
                        }

                        return (string) round(($record->done_tasks_count / $record->tasks_count) * 100) . '%';
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime(),
            ]);
    }
}
