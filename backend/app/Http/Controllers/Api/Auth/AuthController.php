<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('The provided credentials are incorrect.', 401);
        }

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(1));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
        ], 'Login successful');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'university_id' => 1
        ]);
        $user->assignRole('editor');

        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
        ], 'Registration successful', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->has('refresh_token')) {
            $refreshToken = explode('|', $request->refresh_token)[1] ?? null;
            if ($refreshToken) {
                PersonalAccessToken::findToken($refreshToken)?->delete();
            }
        }

        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = explode('|', $request->refresh_token)[1] ?? null;

        if (!$refreshToken) {
            return $this->errorResponse('Invalid refresh token format', 400);
        }

        $token = PersonalAccessToken::findToken($refreshToken);

        if (!$token || $token->name !== 'refresh_token' || !$token->can('refresh-token')) {
            return $this->errorResponse('Invalid refresh token', 401);
        }

        $user = $token->tokenable;

        $newAccessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(1));
        $newRefreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));

        $token->delete();

        return $this->successResponse([
            'access_token' => $newAccessToken->plainTextToken,
            'refresh_token' => $newRefreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));

        return $this->successResponse([
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
        ], 'Password changed successfully');
    }
}