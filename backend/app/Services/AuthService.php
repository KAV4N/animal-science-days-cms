<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log; // Import Log facade
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
        Log::info('Attempting login for email: ' . $email);

        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::warning('Login attempt: User not found with email: ' . $email);
            return false;
        }

        Log::info('Login attempt: User found with ID: ' . $user->id . ' and email: ' . $user->email);
        Log::info('Login attempt: Stored password hash: ' . $user->password);

        if (Hash::check($password, $user->password)) {
            Log::info('Login attempt: Hash::check PASSED for user ID: ' . $user->id);
        } else {
            Log::error('Login attempt: Hash::check FAILED for user ID: ' . $user->id);
        }

        $credentials = ['email' => $email, 'password' => $password];
        Log::info('Login attempt: Credentials for Auth::attempt: ', $credentials);


        if (!Auth::attempt($credentials)) {
            Log::warning('Login attempt: Auth::attempt FAILED for email: ' . $email);
            $userExists = User::where('email', $email)->exists();
            Log::info('Login attempt: Double check if user exists after Auth::attempt failure: ' . ($userExists ? 'Yes' : 'No'));
            return false;
        }

        Log::info('Login attempt: Auth::attempt SUCCEEDED for email: ' . $email);

        $authenticatedUser = Auth::user();
        if (!$authenticatedUser) {
            Log::error('Login attempt: Auth::attempt succeeded but Auth::user() is null for email: ' . $email);
            return false;
        }

        // It's safer to re-fetch the user to ensure we have the latest model instance,
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
        Log::info('Registering user with email: ' . $data['email']);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'first_login' => $data['first_login'] ?? false, // Ensure first_login is handled
            'university_id' => $data['university_id'] ?? null, // Handle optional university_id
        ]);

        // Assign default role if needed
        if (isset($data['role'])) {
            $user->assignRole($data['role']);
            Log::info('Assigned role: ' . $data['role'] . ' to user ID: ' . $user->id);
        }

        $tokens = $this->createTokens($user);
        Log::info('User registered successfully: ID ' . $user->id);

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
        Log::info('Deleted old tokens for user ID: ' . $user->id);

        // Create access token that expires in 1 hour (or from config)
        $accessTokenTTL = config('sanctum.access_token_expiration', 60); // minutes
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes($accessTokenTTL));
        Log::info('Created new access token for user ID: ' . $user->id);

        // Create refresh token that expires in 7 days (or from config)
        $refreshTokenTTL = config('sanctum.refresh_token_expiration', 7 * 24 * 60); // minutes (default 7 days)
        $refreshToken = $user->createToken('refresh_token', ['*'], now()->addMinutes($refreshTokenTTL));
        Log::info('Created new refresh token for user ID: ' . $user->id);


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
        $refreshTokenTTLMinutes = config('sanctum.refresh_token_expiration', 7 * 24 * 60); // Default 7 days in minutes

        return Cookie::make(
            'refresh_token',
            $token,
            $refreshTokenTTLMinutes, // Expiry in minutes
            '/', // Path
            config('session.domain'), // Domain (null for current domain, or from session config)
            config('session.secure'), // Secure (true in production)
            true, // HttpOnly
            false, // Raw
            config('session.same_site', 'lax') // SameSite attribute
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
        Log::info('Logged out and deleted tokens for user ID: ' . $user->id);
        Auth::guard('web')->logout(); // Ensure session is logged out if web guard was used
    }

    /**
     * Refresh the access token using a refresh token
     *
     * @param string|null $refreshToken
     * @return array|false
     */
    public function refreshToken(?string $refreshToken)
    {
        Log::info('Attempting to refresh token.');
        if (!$refreshToken) {
            Log::warning('Refresh token attempt: No refresh token provided.');
            return false;
        }

        // Extract the token ID from the plain text token (format: id|token_hash)
        $tokenParts = explode('|', $refreshToken);
        $tokenId = $tokenParts[0] ?? null;

        if (!$tokenId || !is_numeric($tokenId)) {
            Log::warning('Refresh token attempt: Invalid token format or missing ID.');
            return false;
        }

        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            Log::warning('Refresh token attempt: Token not found or not a refresh token. Token ID: ' . $tokenId);
            return false;
        }

        $user = $tokenModel->tokenable;
        if (!($user instanceof User)) { // Ensure tokenable is a User model
            Log::error('Refresh token attempt: Tokenable entity is not a User. Token ID: ' . $tokenId);
            return false;
        }


        // Verify token has not expired (using configured refresh token expiration)
        $refreshTokenTTLDays = config('sanctum.refresh_token_expiration_days', config('sanctum.refresh_token_expiration', 30) / (24 * 60)); // Prefer days, fallback to minutes from sanctum.php
        if ($tokenModel->created_at->addDays($refreshTokenTTLDays)->isPast()) {
            Log::warning('Refresh token attempt: Token expired. Token ID: ' . $tokenId);
            $tokenModel->delete();
            return false;
        }

        // Delete the old refresh token and create new tokens
        $tokenModel->delete();
        Log::info('Refresh token attempt: Old refresh token deleted. Token ID: ' . $tokenId);
        $tokens = $this->createTokens($user);
        Log::info('Refresh token attempt: New tokens created for user ID: ' . $user->id);


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
        Log::info('Attempting to change password for user ID: ' . $user->id);
        $isFirstLoginScenario = $user->first_login || ($user->first_login && is_null($currentPassword));

        if (!$isFirstLoginScenario && !Hash::check((string) $currentPassword, $user->password)) {
            Log::warning('Change password attempt: Current password incorrect for user ID: ' . $user->id);
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->first_login = false;
        $user->save();
        Log::info('Password changed successfully for user ID: ' . $user->id . '. First login set to false.');

        // Create new tokens as password change should invalidate old sessions/tokens.
        $tokens = $this->createTokens($user);

        return [
            'user' => $user,
            'tokens' => $tokens
        ];
    }
}
