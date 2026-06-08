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
        $user = Auth::user();
        $canManageTasks = $this->canManageTasks();

        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Tiêu đề')
                ->required()
                ->maxLength(255)
                ->disabled(! $canManageTasks),
            Forms\Components\Textarea::make('description')
                ->label('Mô tả')
                ->rows(4)
                ->disabled(! $canManageTasks),
            Forms\Components\Select::make('assignee_ids')
                ->label('Người thực hiện')
                ->multiple()
                ->native(false)
                ->options(fn (): array => $this->getAssignableUsers())
                ->preload()
                ->searchable()
                ->required()
                ->disabled(! $canManageTasks),
            Forms\Components\Select::make('status')
                ->label('Trạng thái')
                ->options([
                    Task::STATUS_NEW => 'New',
                Task::STATUS_PENDING => 'Pending',
                Task::STATUS_IN_PROGRESS => 'In Progress',
                Task::STATUS_CODE_FINISH => 'Code Finish',
                Task::STATUS_CODE_REVIEW => 'Code Review',
                Task::STATUS_REVIEW_DONE => 'Review Done',
                Task::STATUS_TEST_READY => 'Test Ready',
                Task::STATUS_TESTING => 'Testing',
                Task::STATUS_TEST_DONE => 'Test Done',
                Task::STATUS_REJECTED => 'Rejected',
                Task::STATUS_REOPEN => 'Reopen',
                Task::STATUS_CLOSED => 'Closed',
                ])
                ->required()
                ->default(Task::STATUS_NEW),
            Forms\Components\Select::make('priority')
                ->label('Mức ưu tiên')
                ->options([
                    Task::PRIORITY_LOW => 'Thấp',
                    Task::PRIORITY_MEDIUM => 'Trung bình',
                    Task::PRIORITY_HIGH => 'Cao',
                ])
                ->required()
                ->default(Task::PRIORITY_MEDIUM)
                ->disabled(! $canManageTasks),
            Forms\Components\DateTimePicker::make('deadline')
                ->label('Hạn chót')
                ->disabled(! $canManageTasks),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('assignees.name')
                    ->label('Người thực hiện')
                    ->badge(),
                Tables\Columns\BadgeColumn::make('status')->label('Trạng thái')->colors([
                    'gray' => Task::STATUS_NEW,
                    'secondary' => Task::STATUS_PENDING,
                    'warning' => Task::STATUS_IN_PROGRESS,
                    'info' => Task::STATUS_CODE_FINISH,
                    'primary' => Task::STATUS_CODE_REVIEW,
                    'success' => [Task::STATUS_REVIEW_DONE, Task::STATUS_TEST_READY, Task::STATUS_TESTING, Task::STATUS_TEST_DONE, Task::STATUS_CLOSED],
                    'danger' => [Task::STATUS_REJECTED, Task::STATUS_REOPEN],
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
                    Task::STATUS_NEW => 'New',
                Task::STATUS_PENDING => 'Pending',
                Task::STATUS_IN_PROGRESS => 'In Progress',
                Task::STATUS_CODE_FINISH => 'Code Finish',
                Task::STATUS_CODE_REVIEW => 'Code Review',
                Task::STATUS_REVIEW_DONE => 'Review Done',
                Task::STATUS_TEST_READY => 'Test Ready',
                Task::STATUS_TESTING => 'Testing',
                Task::STATUS_TEST_DONE => 'Test Done',
                Task::STATUS_REJECTED => 'Rejected',
                Task::STATUS_REOPEN => 'Reopen',
                Task::STATUS_CLOSED => 'Closed',
                ]),
                Tables\Filters\SelectFilter::make('assignee_id')
                    ->label('Người thực hiện')
                    ->options(fn (): array => $this->getAssignableUsers()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tạo công việc')
                    ->authorize(fn (): bool => $this->canManageTasks())
                    ->visible(fn (): bool => $this->canManageTasks())
                    ->mutateFormDataUsing(function (array $data): array {
                        $assigneeIds = array_values(array_unique(array_filter((array) ($data['assignee_ids'] ?? []))));

                        $data['creator_id'] = Auth::id();
                        $data['assignee_id'] = $assigneeIds[0] ?? null;
                        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_CLOSED ? now() : null;

                        return $data;
                    })
                    ->after(function (Model $record): void {
                        $assigneeIds = array_values(array_unique(array_filter((array) request()->input('assignee_ids', []))));

                        if (count($assigneeIds) > 0) {
                            $record->assignees()->sync($assigneeIds);
                        } elseif ($record->assignee_id !== null) {
                            $record->assignees()->sync([$record->assignee_id]);
                        }

                        $this->writeActivityLog($record, 'created');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->authorize(fn (Model $record): bool => $this->canManageTasks() || $record->project->isMember(Auth::user()))
                    ->fillForm(function (Model $record): array {
                        $assigneeIds = $record->assignees()
                            ->pluck('users.id')
                            ->map(fn ($id): string => (string) $id)
                            ->toArray();

                        if (empty($assigneeIds) && $record->assignee_id !== null) {
                            $assigneeIds = [(string) $record->assignee_id];
                        }

                        return [
                            'title' => $record->title,
                            'description' => $record->description,
                            'assignee_ids' => $assigneeIds,
                            'status' => $record->status,
                            'priority' => $record->priority,
                            'deadline' => $record->deadline,
                        ];
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $assigneeIds = array_values(array_unique(array_filter((array) ($data['assignee_ids'] ?? []))));

                        $data['assignee_id'] = $assigneeIds[0] ?? null;
                        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_CLOSED ? now() : null;

                        return $data;
                    })
                    ->after(function (Model $record): void {
                        $assigneeIds = array_values(array_unique(array_filter((array) request()->input('assignee_ids', []))));

                        if (count($assigneeIds) > 0) {
                            $record->assignees()->sync($assigneeIds);
                        } elseif ($record->assignee_id !== null) {
                            $record->assignees()->sync([$record->assignee_id]);
                        }

                        $this->writeActivityLog($record, 'updated');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn (): bool => $this->canManageTasks())
                    ->visible(fn (): bool => $this->canManageTasks()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn (): bool => $this->canManageTasks())
                        ->visible(fn (): bool => $this->canManageTasks()),
                ]),
            ]);
    }

    private function canManageTasks(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return $this->getOwnerRecord()->isProjectLeader($user);
    }

    private function getAssignableUsers(): array
    {
        return $this->getOwnerRecord()
            ->members()
            ->pluck('users.name', 'users.id')
            ->mapWithKeys(fn ($name, $id): array => [(string) $id => $name])
            ->toArray();
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
