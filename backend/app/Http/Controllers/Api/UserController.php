<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user with roles and permissions.
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $roles = $user->roles->pluck('name');
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions
            ]
        ], 200);
    }
}