<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\AuthenticationServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticationService implements AuthenticationServiceInterface
{
    public function login(string $email, string $password): User
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        request()->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        $request = request();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
