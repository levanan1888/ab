<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskCommentResource\Pages;
use App\Models\Task;
use App\Models\TaskComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskCommentResource extends Resource
{
    protected static ?string $model = TaskComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Bình luận';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('task_id')
                ->label('Công việc')
                ->options(Task::query()->pluck('title', 'id')->toArray())
                ->required()
                ->searchable(),
            Forms\Components\Textarea::make('content')->label('Nội dung')->required()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('task.title')->label('Công việc')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Người dùng'),
                Tables\Columns\TextColumn::make('content')->label('Nội dung')->limit(60),
                Tables\Columns\TextColumn::make('created_at')->label('Tạo lúc')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTaskComments::route('/'),
            'create' => Pages\CreateTaskComment::route('/create'),
            'edit' => Pages\EditTaskComment::route('/{record}/edit'),
        ];
    }
}
