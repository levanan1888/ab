<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function isReadOnly(): bool
    {
        return Auth::user()?->role !== User::ROLE_LEADER;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('role_in_project')
                ->label('Vai trò trong nhóm')
                ->options([
                    User::ROLE_LEADER => 'Quản lý nhóm làm việc',
                    User::ROLE_MEMBER => 'Thành viên',
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('pivot.role_in_project')
                    ->label('Vai trò trong nhóm'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Thêm thành viên')
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER)
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->where('role', User::ROLE_MEMBER)
                        ->orderBy('name'))
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Người dùng')
                            ->placeholder('Chọn thành viên')
                            ->optionsLimit(100),
                        Forms\Components\Select::make('role_in_project')
                            ->label('Vai trò trong nhóm')
                            ->options([
                                User::ROLE_LEADER => 'Quản lý nhóm làm việc',
                                User::ROLE_MEMBER => 'Thành viên',
                            ])
                            ->required()
                            ->default(User::ROLE_MEMBER),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn ($record): bool => (int) $record->id === (int) Auth::id())
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
                Tables\Actions\DetachAction::make()
                    ->disabled(fn ($record): bool => (int) $record->id === (int) Auth::id())
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
            ]);
    }
}
