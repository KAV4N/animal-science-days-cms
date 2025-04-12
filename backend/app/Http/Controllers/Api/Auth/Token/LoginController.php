<?php

namespace App\Http\Controllers\Api\Auth\Token;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        $roles = $user->roles->pluck('name');
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully authenticated',
            'data' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions,
                'token' => $token
            ]
        ], 200);
    }
}