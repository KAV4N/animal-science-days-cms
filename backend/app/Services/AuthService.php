<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthService
{
    /**
     * Create a refresh token cookie
     * 
     * @param string $tokenValue The plaintext token value
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function createRefreshTokenCookie(string $tokenValue)
    {
        $secure = config('app.env') !== 'local';
        $sameSite = $secure ? 'None' : 'Lax';
        
        return Cookie::make(
            'refresh_token',
            $tokenValue,
            config('sanctum.refresh_token_expiration', 30) * 24 * 60, // Convert days to minutes
            '/',
            null, // Domain (null = current domain)
            $secure,
            true, // HTTP only
            false, // Raw
            $sameSite
        );
    }

    /**
     * Create standardized tokens for a user
     * 
     * @param User $user
     * @return array with access token and refresh token
     */
    public function createUserTokens(User $user): array
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

    /**
     * Attempt to authenticate a user
     * 
     * @param string $email
     * @param string $password
     * @return array|false Returns user data with tokens if successful, false otherwise
     */
    public function attemptLogin(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        $user->tokens()->delete();
        $tokens = $this->createUserTokens($user);
        
        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Register a new user
     * 
     * @param array $userData
     * @return array User data with tokens
     */
    public function registerUser(array $userData)
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'university_id' => 1 
        ]);
        
        $user->assignRole('editor');

        $tokens = $this->createUserTokens($user);
        
        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }

    /**
     * Logout a user by revoking their tokens
     * 
     * @param User $user
     * @return bool
     */
    public function logout(User $user)
    {
        try {
            $user->currentAccessToken()->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refresh tokens using a refresh token
     * 
     * @param string|null $refreshToken
     * @return array|false Returns user data with tokens if successful, false otherwise
     */
    public function refreshToken(?string $refreshToken)
    {
        if (!$refreshToken) {
            return false;
        }

        try {
        
            [$tokenId, $tokenPart] = explode('|', $refreshToken, 2);
            $token = PersonalAccessToken::find($tokenId);
            if (!$token || 
                $token->name !== 'refresh_token' || 
                !$token->can('refresh-token') ||
                !hash_equals($token->token, hash('sha256', $tokenPart))) {
                
                return false;
            }

            $user = $token->tokenable;
            $token->delete();

            $tokens = $this->createUserTokens($user);
            
            return [
                'user' => $user,
                'tokens' => $tokens
            ];
        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change user's password
     * 
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return array|false Returns tokens if successful, false otherwise
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        $user->tokens()->delete();
        $tokens = $this->createUserTokens($user);

        return [
            'tokens' => $tokens
        ];
    }
}