<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getTitle(): string
    {
        return 'Đăng nhập';
    }

    public function getHeading(): string
    {
        return 'Đăng nhập hệ thống';
    }

    public function getSubheading(): ?string
    {
        return 'Vui lòng nhập thông tin tài khoản để tiếp tục';
    }

    protected function getEmailFormComponent(): TextInput
    {
        return parent::getEmailFormComponent()->label('Email');
    }

    protected function getPasswordFormComponent(): TextInput
    {
        return parent::getPasswordFormComponent()->label('Mật khẩu');
    }

    protected function getRememberFormComponent(): Checkbox
    {
        return parent::getRememberFormComponent()->label('Ghi nhớ đăng nhập');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()->label('Đăng nhập');
    }

    public function registerAction(): Action
    {
        return parent::registerAction()->label('Đăng ký tài khoản');
    }
}
