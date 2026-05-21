<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\MembersRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Dự án';

    public static function form(Form $form): Form
    {
        $is_member = Auth::user()?->role === User::ROLE_MEMBER;

        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Tên dự án')->required()->maxLength(255)->disabled($is_member),
            Forms\Components\Textarea::make('description')->label('Mô tả')->rows(3)->disabled($is_member),
            Forms\Components\Toggle::make('is_active')->label('Đang hoạt động')->default(true)->disabled($is_member),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Tên dự án')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('owner.name')->label('Quản lý dự án')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Hoạt động')->boolean(),
                Tables\Columns\TextColumn::make('members_count')->counts('members')->label('Số thành viên'),
                Tables\Columns\TextColumn::make('tasks_count')->counts('tasks')->label('Số công việc'),
                Tables\Columns\TextColumn::make('created_at')->label('Tạo lúc')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Trạng thái hoạt động'),
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

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
            TasksRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            return $query->whereHas('members', function (Builder $builder) use ($user): void {
                $builder->where('users.id', $user->id);
            });
        }

        return $query;
    }


    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ($user->role === \App\Models\User::ROLE_LEADER) {
            return true;
        }

        return $user->projects()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
