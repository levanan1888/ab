<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AppearanceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Cài đặt giao diện';
    protected static ?string $title = 'Cài đặt logo & favicon';
    protected static ?string $navigationGroup = 'Cài đặt';
    protected static string $view = 'filament.pages.appearance-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->is_admin() === true;
    }

    public function mount(): void
    {
        $setting = SiteSetting::query()->firstOrCreate(['id' => 1], []);

        $this->form->fill([
            'logo_path' => $setting->logo_path,
            'favicon_path' => $setting->favicon_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo website')
                    ->image()
                    ->disk('public')
                    ->directory('site-settings')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->imageEditor()
                    ->maxSize(2048),
                Forms\Components\FileUpload::make('favicon_path')
                    ->label('Favicon website')
                    ->image()
                    ->disk('public')
                    ->directory('site-settings')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->imageEditor()
                    ->maxSize(1024),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $setting = SiteSetting::query()->firstOrCreate(['id' => 1], []);

        $setting->update([
            'logo_path' => $state['logo_path'] ?? null,
            'favicon_path' => $state['favicon_path'] ?? null,
        ]);

        Notification::make()->title('Đã cập nhật logo và favicon')->success()->send();
    }
}
