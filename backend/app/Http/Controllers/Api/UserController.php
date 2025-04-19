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

    //TODO: add more user functions for CRUD operations
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $roles = $user->roles->pluck('name');
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'data' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions
            ],
            'message'=>'success'
        ], 200);
    }
}