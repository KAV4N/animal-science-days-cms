<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Attempt to login a user
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function attemptLogin(string $email, string $password)
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return false;
        }

        $user = User::where('email', $email)->first();
        $tokens = $this->createTokens($user);

        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function registerUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_login' => false, // New users set their own password
        ]);

        // Assign default role if needed
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        $tokens = $this->createTokens($user);

        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Create access and refresh tokens for a user
     *
     * @param User $user
     * @return array
     */
    private function createTokens(User $user)
    {
        // Delete old tokens
        $user->tokens()->delete();

        // Create access token that expires in 1 hour
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());

        // Create refresh token that expires in 7 days
        $refreshToken = $user->createToken('refresh_token', ['*'], now()->addDays(7));

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Create a refresh token cookie
     *
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function createRefreshTokenCookie(string $token)
    {
        return Cookie::make(
            'refresh_token',
            $token,
            60 * 24 * 7, // 7 days in minutes
            '/',
            null,
            config('app.env') === 'production',
            true,
            false,
            'lax'
        );
    }

    /**
     * Logout a user and revoke tokens
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user)
    {
        $user->tokens()->delete();
    }

    /**
     * Refresh the access token using a refresh token
     *
     * @param string|null $refreshToken
     * @return array|false
     */
    public function refreshToken(?string $refreshToken)
    {
        if (!$refreshToken) {
            return false;
        }

        // Extract the token ID and hash from the plain text token
        $tokenId = explode('|', $refreshToken)[0] ?? null;

        if (!$tokenId) {
            return false;
        }

        // Find the token by ID
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            return false;
        }

        $user = $tokenModel->tokenable;

        // Verify token has not expired
        if ($tokenModel->created_at->addDays(7)->isPast()) {
            $tokenModel->delete();
            return false;
        }

        // Delete the old refresh token and create new tokens
        $tokenModel->delete();
        $tokens = $this->createTokens($user);

        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Change user password
     *
     * @param User $user
     * @param string|null $currentPassword
     * @param string $newPassword
     * @return array|false
     */
    public function changePassword(User $user, ?string $currentPassword, string $newPassword)
    {
        // For first login, we don't need to verify current password
        if (!$user->first_login && !Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->first_login = false;
        $user->save();

        // Create new tokens
        $tokens = $this->createTokens($user);

        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }
}
