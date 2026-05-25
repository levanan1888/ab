<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\MyPage;
use App\Filament\Pages\UserProfilePage;
use App\Filament\Pages\TeamMembersPage;
use App\Filament\Pages\TaskPriorityBoard;
use App\Filament\Widgets\TaskStatsOverview;
use App\Filament\Widgets\ProjectProgressTable;
use App\Http\Middleware\EnsureMemberHasProject;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Quản lý nhóm làm việc')
            ->brandLogo(asset('img/avatThanh.png'))
            ->brandLogoHeight('4rem')
            ->darkMode(false)
            ->maxContentWidth(MaxWidth::Full)
            ->login(Login::class)
            ->registration(Register::class)
            ->profile()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                MyPage::class,
                TaskPriorityBoard::class,
                TeamMembersPage::class,
                UserProfilePage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                TaskStatsOverview::class,
                ProjectProgressTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureMemberHasProject::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
