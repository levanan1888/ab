<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Công việc';

    public function form(Form $form): Form
    {
        $is_member = Auth::user()?->role === User::ROLE_MEMBER;

        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Tiêu đề')
                ->required()
                ->maxLength(255)
                ->disabled($is_member),
            Forms\Components\Textarea::make('description')
                ->label('Mô tả')
                ->rows(4)
                ->disabled($is_member),
            Forms\Components\Select::make('assignee_id')
                ->label('Người thực hiện')
                ->options(fn (): array => $this->getAssignableUsers())
                ->searchable()
                ->disabled($is_member),
            Forms\Components\Select::make('status')
                ->label('Trạng thái')
                ->options([
                    Task::STATUS_TODO => 'Chưa làm',
                    Task::STATUS_IN_PROGRESS => 'Đang làm',
                    Task::STATUS_DONE => 'Hoàn thành',
                ])
                ->required()
                ->default(Task::STATUS_TODO),
            Forms\Components\Select::make('priority')
                ->label('Mức ưu tiên')
                ->options([
                    Task::PRIORITY_LOW => 'Thấp',
                    Task::PRIORITY_MEDIUM => 'Trung bình',
                    Task::PRIORITY_HIGH => 'Cao',
                ])
                ->required()
                ->default(Task::PRIORITY_MEDIUM)
                ->disabled($is_member),
            Forms\Components\DateTimePicker::make('deadline')
                ->label('Hạn chót')
                ->disabled($is_member),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Người thực hiện'),
                Tables\Columns\BadgeColumn::make('status')->label('Trạng thái')->colors([
                    'gray' => Task::STATUS_TODO,
                    'warning' => Task::STATUS_IN_PROGRESS,
                    'success' => Task::STATUS_DONE,
                ]),
                Tables\Columns\BadgeColumn::make('priority')->label('Mức ưu tiên')->colors([
                    'success' => Task::PRIORITY_LOW,
                    'warning' => Task::PRIORITY_MEDIUM,
                    'danger' => Task::PRIORITY_HIGH,
                ]),
                Tables\Columns\TextColumn::make('deadline')->label('Hạn chót')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Trạng thái')->options([
                    Task::STATUS_TODO => 'Chưa làm',
                    Task::STATUS_IN_PROGRESS => 'Đang làm',
                    Task::STATUS_DONE => 'Hoàn thành',
                ]),
                Tables\Filters\SelectFilter::make('assignee_id')
                    ->label('Người thực hiện')
                    ->options(fn (): array => $this->getAssignableUsers()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tạo công việc')
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['creator_id'] = Auth::id();
                        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_DONE ? now() : null;

                        return $data;
                    })
                    ->after(function (Model $record): void {
                        $this->writeActivityLog($record, 'created');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_DONE ? now() : null;

                        return $data;
                    })
                    ->after(function (Model $record): void {
                        $this->writeActivityLog($record, 'updated');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
                ]),
            ]);
    }

    private function getAssignableUsers(): array
    {
        return $this->getOwnerRecord()->members()->pluck('users.name', 'users.id')->toArray();
    }

    private function writeActivityLog(Model $record, string $action): void
    {
        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $record->id,
            'action' => $action,
            'causer_id' => Auth::id(),
            'meta' => ['status' => $record->status],
        ]);
    }
}
