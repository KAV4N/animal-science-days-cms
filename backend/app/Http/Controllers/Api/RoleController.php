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

    public function availableRoles(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized access', 403);
        }

        $roles = collect();

        if ($user->hasPermissionTo('manage.admin')) {
            $roles = Role::whereIn('name', ['admin', 'editor'])->get(['id', 'name']);
        } elseif ($user->hasPermissionTo('manage.editor')) {
            $roles = Role::where('name', 'editor')->get(['id', 'name']);
        }

        return $this->successResponse($roles, 'Available roles retrieved successfully');
    }
}
