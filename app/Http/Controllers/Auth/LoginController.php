<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function authenticate(Request $request): ?LoginResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'], // Este será el campo de username en realidad
            'password' => ['required', 'string'],
        ]);

        // Intentar login con username
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attemptCredentials = [
            $loginField => $credentials['email'],
            'password' => $credentials['password'],
        ];

        // Verificar que el usuario esté activo
        $user = \App\Models\User::where($loginField, $credentials['email'])->first();

        if ($user && !$user->active) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        if (! Auth::attempt($attemptCredentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        $request->session()->regenerate();

        return app(LoginResponse::class);
    }
}
