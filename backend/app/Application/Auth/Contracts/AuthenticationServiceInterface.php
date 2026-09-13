<?php

namespace App\Application\Auth\Contracts;

use App\Models\User;

interface AuthenticationServiceInterface
{
    public function login(string $email, string $password): User;

    public function logout(): void;
}
