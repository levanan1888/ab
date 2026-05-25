<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
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
                    'leader' => 'Quản lý nhóm làm việc',
                    'member' => 'Thành viên',
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Họ tên')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email'),
                Tables\Columns\TextColumn::make('pivot.role_in_project')->label('Vai trò trong nhóm'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Thêm thành viên')
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER)
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()->label('Người dùng'),
                        Forms\Components\Select::make('role_in_project')
                            ->label('Vai trò trong nhóm')
                            ->options([
                                'leader' => 'Quản lý nhóm làm việc',
                                'member' => 'Thành viên',
                            ])
                            ->required()
                            ->default('member'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
                Tables\Actions\DetachAction::make()
                    ->visible(fn (): bool => Auth::user()?->role === User::ROLE_LEADER),
            ]);
    }
}
