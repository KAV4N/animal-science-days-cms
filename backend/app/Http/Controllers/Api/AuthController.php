<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\ConferenceLockService;
use App\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Conference lock service instance
     * 
     * @var ConferenceLockService
     */
    protected $conferenceLockService;

    /**
     * Create a new controller instance
     * 
     * @param ConferenceLockService $conferenceLockService
     * @return void
     */
    public function __construct(ConferenceLockService $conferenceLockService)
    {
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
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('The provided credentials are incorrect.', 401);
        }

        $user->tokens()->delete();

        $tokens = $this->createUserTokens($user);

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $tokens['access_token']->plainTextToken,
            'refresh_token' => $tokens['refresh_token']->plainTextToken,
        ], 'Login successful');
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

            $user->tokens()->delete();
            
            return $this->successResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return $this->errorResponse('Log out failed', 500);
        }
    }

    /**
     * Refresh access token using refresh token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->input('refresh_token');
            
            if (!$refreshToken) {
                return $this->errorResponse('Refresh token not provided', 401);
            }
            
            $tokenModel = PersonalAccessToken::findToken($refreshToken);
            
            if (!$tokenModel || !$tokenModel->can('refresh-token') || $tokenModel->expires_at->isPast()) {
                return $this->errorResponse('Invalid or expired refresh token'.$tokenModel, 401);
            }
            
            $user = $tokenModel->tokenable;

            $user->tokens()->delete();
            
            $tokens = $this->createUserTokens($user);

            return $this->successResponse([
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token']->plainTextToken,
                'refresh_token' => $tokens['refresh_token']->plainTextToken,
            ], 'Token refreshed successfully');
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
        try {
            $user = auth()->user();

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
                'must_change_password' => false,
            ]);

            $user->tokens()->delete();

            $tokens = $this->createUserTokens($user);

            return $this->successResponse([
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token']->plainTextToken,
                'refresh_token' => $tokens['refresh_token']->plainTextToken,
            ], 'Password changed successfully');
        } catch (\Exception $e) {
            Log::error('Password change failed: ' . $e->getMessage());
            return $this->errorResponse('Unable to change password. Please try again.', 500);
        }
    }

    /**
     * Create standardized tokens for a user
     * 
     * @param User $user
     * @return array with access token and refresh token
     */
    private function createUserTokens(User $user): array
    {
        $accessTokenExpiration = config('sanctum.access_token_expiration', 60);
        $refreshTokenExpiration = config('sanctum.refresh_token_expiration', 30);
        
        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes($accessTokenExpiration)
        );
        
        $refreshToken = $user->createToken(
            'refresh_token',
            ['refresh-token'],
            now()->addDays($refreshTokenExpiration)
        );
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }
}