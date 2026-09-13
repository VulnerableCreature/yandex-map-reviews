<?php

namespace App\Http\Controllers\Api;

use App\Application\Auth\Contracts\AuthenticationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthenticationServiceInterface $authentication) {}

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json([
            'user' => $this->authentication->login(
                $request->validated('email'),
                $request->validated('password'),
            ),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authentication->logout();

        return response()->json(['message' => 'ok']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
