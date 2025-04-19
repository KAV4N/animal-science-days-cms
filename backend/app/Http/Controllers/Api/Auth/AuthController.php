<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));
        
        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
            ]
        ], 200);
    }


    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'university_id'=>1
        ]);
        $user->assignRole('editor');
        
        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));

        return response()->json([
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
            ]
        ], 201);
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

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = explode('|', $request->refresh_token)[1] ?? null;
        
        if (!$refreshToken) {
            return response()->json([
                'message' => 'Invalid refresh token format'
            ], 400);
        }
        
        $token = PersonalAccessToken::findToken($refreshToken);
        
        if (!$token || $token->name !== 'refresh_token' || !$token->can('refresh-token')) {
            return response()->json([
                'message' => 'Invalid refresh token'
            ], 401);
        }
        
        $user = $token->tokenable;
        
        $newAccessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $newRefreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));
        
        $token->delete();
        
        return response()->json([
            'access_token' => $newAccessToken->plainTextToken,
            'refresh_token' => $newRefreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ], 200);
    }


    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        $user->tokens()->delete();
        
        $accessToken = $user->createToken('access_token', ['*'], Carbon::now()->addMinutes(60));
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], Carbon::now()->addDays(30));
        
        return response()->json([
            'message' => 'Password changed successfully',
            'success' => true,
            'data' => [
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
            ]
        ], 200);
    }
}