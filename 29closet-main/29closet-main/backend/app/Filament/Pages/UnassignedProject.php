<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UnassignedProject extends Page
{
    protected static ?string $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.unassigned-project';

    protected static ?string $slug = 'chua-duoc-phan-vao-du-an';

    protected static ?string $title = 'Bạn chưa được phân vào nhóm';
}
