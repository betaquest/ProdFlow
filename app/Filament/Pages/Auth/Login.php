<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Usuario')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->placeholder('Ingresa tu nombre de usuario');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $loginField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $loginField => $data['email'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        $credentials = $this->getCredentialsFromFormData($data);

        \Illuminate\Support\Facades\Log::info('Login attempt', [
            'credentials' => array_keys($credentials),
            'login_value' => $credentials[array_key_first($credentials)]
        ]);

        // Verificar que el usuario esté activo
        $loginField = array_key_first($credentials);
        $user = \App\Models\User::where($loginField, $credentials[$loginField])->first();

        if (!$user) {
            \Illuminate\Support\Facades\Log::warning('User not found', ['field' => $loginField, 'value' => $credentials[$loginField]]);
            $this->throwFailureValidationException();
        }

        \Illuminate\Support\Facades\Log::info('User found', ['user' => $user->username, 'active' => $user->active]);

        if (!$user->active) {
            \Illuminate\Support\Facades\Log::warning('User not active');
            $this->throwFailureValidationException();
        }

        if (! \Illuminate\Support\Facades\Auth::attempt($credentials, $data['remember'] ?? false)) {
            \Illuminate\Support\Facades\Log::error('Auth attempt failed', ['credentials' => array_keys($credentials)]);
            $this->throwFailureValidationException();
        }

        \Illuminate\Support\Facades\Log::info('Login successful');

        session()->regenerate();

        return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
    }
}
