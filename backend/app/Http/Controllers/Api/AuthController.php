<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $result = $this->authService->registerUser($request->validated());

        $user = $result['user'];
        $tokens = $result['tokens'];

        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'access_token' => $tokens['access_token']->plainTextToken,
            'first_login' => false,
        ], 'Registration successful', 201)->withCookie($refreshTokenCookie);
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $result = $this->authService->attemptLogin($request->email, $request->password);

        if (!$result) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $user = $result['user'];
        $tokens = $result['tokens'];

        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'access_token' => $tokens['access_token']->plainTextToken,
            'first_login' => $user->first_login,
        ], 'Login successful')->withCookie($refreshTokenCookie);
    }

    /**
     * Change user password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $result = $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password
        );

        if (!$result) {
            return $this->errorResponse('Current password is incorrect', 401);
        }

        $tokens = $result['tokens'];
        $user = $result['user'];

        $refreshTokenCookie = $this->authService->createRefreshTokenCookie(
            $tokens['refresh_token']->plainTextToken
        );

        return $this->successResponse([
            'access_token' => $tokens['access_token']->plainTextToken,
            'first_login' => $user->first_login,
        ], 'Password changed successfully')->withCookie($refreshTokenCookie);
    }

    /**
     * Get authenticated user information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'User data retrieved successfully');
    }
}
