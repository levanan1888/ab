<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Công việc';

    public static function form(Form $form): Form
    {
        $is_member = Auth::user()?->role === User::ROLE_MEMBER;

        return $form->schema([
            Forms\Components\Select::make('project_id')
                ->label('Nhóm làm việc')
                ->options(Project::query()->pluck('name', 'id')->toArray())
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('assignee_id', null);
                })
                ->disabled($is_member),
            Forms\Components\TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->disabled($is_member),
            Forms\Components\Textarea::make('description')->label('Mô tả')->rows(4)->disabled($is_member),
            Forms\Components\Select::make('assignee_id')
                ->label('Người thực hiện')
                ->options(function (Get $get): array {
                    $project_id = $get('project_id');

                    if (empty($project_id)) {
                        return [];
                    }

                    $project = Project::query()->find($project_id);

                    if ($project === null) {
                        return [];
                    }

                    return $project->members()->pluck('users.name', 'users.id')->toArray();
                })
                ->searchable()
                ->disabled($is_member),
            Forms\Components\Select::make('status')->label('Trạng thái')->options([
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
            ])->required()->default(Task::STATUS_NEW),
            Forms\Components\Select::make('priority')->label('Mức ưu tiên')->options([
                Task::PRIORITY_LOW => 'Thấp',
                Task::PRIORITY_MEDIUM => 'Trung bình',
                Task::PRIORITY_HIGH => 'Cao',
            ])->required()->default(Task::PRIORITY_MEDIUM)->disabled($is_member),
            Forms\Components\DateTimePicker::make('deadline')->label('Hạn chót')->disabled($is_member),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('project.name')->label('Nhóm làm việc')->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Người thực hiện'),
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
                Tables\Columns\TextColumn::make('deadline')->label('Hạn chót')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')->label('Nhóm làm việc')->relationship('project', 'name'),
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
                Tables\Filters\SelectFilter::make('assignee_id')->label('Người thực hiện')->relationship('assignee', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            return $query->whereHas('project.members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        return $query;
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
