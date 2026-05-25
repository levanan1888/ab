<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;

class UserProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'users/{user}/profile';

    protected static string $view = 'filament.pages.user-profile-page';

    public User $userRecord;
    public ?array $data = [];

    public function mount($user): void
    {
        abort_unless(Auth::user()?->is_leader() === true, 403);
        $this->userRecord = User::query()->with('projects:id,name')->findOrFail($user);

        $this->form->fill([
            'name' => $this->userRecord->name,
            'email' => $this->userRecord->email,
            'role' => $this->userRecord->role,
        ]);
    }

    public function getTitle(): string
    {
        return 'Thông tin thành viên';
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                TextInput::make('name')->label('Họ tên')->required()->maxLength(255),
                TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                Select::make('role')
                    ->label('Vai trò hệ thống')
                    ->options([
                        User::ROLE_LEADER => 'Leader',
                        User::ROLE_MEMBER => 'Member',
                    ])
                    ->required(),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->userRecord->update([
            'name' => $state['name'],
            'email' => $state['email'],
            'role' => $state['role'],
        ]);

        Notification::make()->title('Đã cập nhật thông tin thành viên')->success()->send();
    }
}
