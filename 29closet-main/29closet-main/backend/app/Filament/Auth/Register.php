<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function getTitle(): string
    {
        return 'Đăng ký';
    }

    public function getHeading(): string
    {
        return 'Tạo tài khoản mới';
    }

    public function getSubheading(): ?string
    {
        return 'Điền thông tin bên dưới để tạo tài khoản';
    }

    protected function getNameFormComponent(): TextInput
    {
        return parent::getNameFormComponent()->label('Họ và tên');
    }

    protected function getEmailFormComponent(): TextInput
    {
        return parent::getEmailFormComponent()->label('Email');
    }

    protected function getPasswordFormComponent(): TextInput
    {
        return parent::getPasswordFormComponent()->label('Mật khẩu');
    }

    protected function getPasswordConfirmationFormComponent(): TextInput
    {
        return parent::getPasswordConfirmationFormComponent()->label('Xác nhận mật khẩu');
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()->label('Đăng ký');
    }

    public function loginAction(): Action
    {
        return parent::loginAction()->label('Quay lại đăng nhập');
    }
}
