<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use App\Services\ConferenceLockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log; 
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Auth service instance
     * 
     * @var AuthService
     */
    protected $authService;

    /**
     * Conference lock service instance
     * 
     * @var ConferenceLockService
     */
    protected $conferenceLockService;

    /**
     * Create a new controller instance
     * 
     * @param AuthService $authService
     * @param ConferenceLockService $conferenceLockService
     * @return void
     */
    public function __construct(AuthService $authService, ConferenceLockService $conferenceLockService)
    {
        $this->authService = $authService;
        $this->conferenceLockService = $conferenceLockService;
    }

    /**
     * Login user and create tokens
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->attemptLogin(
            $request->email, 
            $request->password
        );

        if (!$result) {
            return $this->errorResponse('The provided credentials are incorrect.', 401);
        }

        $user = $result['user'];
        $tokens = $result['tokens'];
        
        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $tokens['access_token']->plainTextToken,
        ], 'Login successful')->withCookie($refreshTokenCookie);
    }

    /**
     * Logout user and revoke tokens
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            

            $this->conferenceLockService->releaseAllUserLocks($user->id);

            $request->user()->tokens()->delete();
            
            $refreshTokenCookie = Cookie::forget('refresh_token');
            
            return $this->successResponse(null, 'Logged out successfully')->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            return $this->errorResponse('Log out failed', false, $e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token cookie
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshTokenFromCookie = $request->cookie('refresh_token');
            
            $tokenModel = PersonalAccessToken::findToken($refreshTokenFromCookie);
            
            if (!$tokenModel || !$tokenModel->can('refresh-token') || $tokenModel->expires_at->isPast()) {
                return $this->errorResponse('Invalid refresh token', 401);
            }
            
            $user = $tokenModel->tokenable;

            $user->tokens()->delete();
            
            $tokens = $this->authService->createUserTokens($user);
            
            $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
                $tokens['refresh_token']->plainTextToken
            );

            return $this->successResponse([
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token']->plainTextToken,
            ], 'Token refreshed successfully')->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return $this->errorResponse('Token refresh failed', 500);
        }
    }

    /**
     * Change user password
     * 
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $user->update([
                'password' => Hash::make($request->new_password),
                'must_change_password' => false,
            ]);

            $user->tokens()->delete();

            $tokens = $this->authService->createUserTokens($user);
            $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
                $tokens['refresh_token']->plainTextToken
            );

            return $this->successResponse([
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token']->plainTextToken,
            ], 'Password changed successfully')->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            return $this->errorResponse('Unable to change password. Please try again.', 500);
        }
    }
}