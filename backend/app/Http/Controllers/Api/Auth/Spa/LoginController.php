<?php

namespace App\Http\Controllers\Api\Auth\Spa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            if (Auth::attempt($request->only('email', 'password'))) {
                $request->session()->regenerate();
               
                $user = Auth::user();
        
                $roles = $user->roles->pluck('name');
                $permissions = $user->getAllPermissions()->pluck('name');

               
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'roles' => $roles,
                        'permissions' => $permissions
                    ]
                ], 200);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }
}