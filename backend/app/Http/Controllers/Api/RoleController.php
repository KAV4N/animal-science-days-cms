<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized access', 403);
        }
        
        $query = Role::query();
        
        if ($user->hasRole('admin') && !$user->hasRole('super_admin')) {
            $query->where('name', 'editor');
        }
        
        $roles = $query->get(['id', 'name']);
        
        return $this->successResponse($roles, 'Roles retrieved successfully');
    }
    
    public function availableRoles(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized access', 403);
        }
        
        $roles = [];
        
        if ($user->hasPermissionTo('manage.admin')) {
            $roles = [
                ['id' => 'editor', 'name' => 'editor'],
                ['id' => 'admin', 'name' => 'admin']
            ];
        } else if ($user->hasPermissionTo('manage.editor')) {
            $roles = [
                ['id' => 'editor', 'name' => 'editor']
            ];
        }
        
        return $this->successResponse($roles, 'Available roles retrieved successfully');
    }
}