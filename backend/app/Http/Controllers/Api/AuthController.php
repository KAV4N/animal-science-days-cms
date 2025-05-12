<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log; 

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
     * Create a new controller instance
     * 
     * @param AuthService $authService
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
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
     * Register a new user
     * 
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->registerUser($request->validated());
        
        $user = $result['user'];
        $tokens = $result['tokens'];
        
        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'user' => new UserResource($user),
           
            'access_token' => $tokens['access_token']->plainTextToken,
        ], 'Registration successful', 201)->withCookie($refreshTokenCookie);
    }

    /**
     * Logout user and revoke tokens
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        
        $refreshTokenCookie = Cookie::forget('refresh_token');
        
        return $this->successResponse(null, 'Logged out successfully')->withCookie($refreshTokenCookie);
    }

    /**
     * Refresh access token using refresh token cookie
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshTokenFromCookie = $request->cookie('refresh_token');
        
        $result = $this->authService->refreshToken($refreshTokenFromCookie);
        
        if (!$result) {
            return $this->errorResponse('Invalid refresh token', 401);
        }
        
        $user = $result['user'];
        $tokens = $result['tokens'];
        
        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $tokens['access_token']->plainTextToken,
        ], 'Token refreshed successfully')->withCookie($refreshTokenCookie);
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