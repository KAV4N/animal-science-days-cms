<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('roles') && !empty($request->roles)) {
            $roles = explode(',', $request->roles);
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortField = in_array($request->sort_field, ['name', 'email', 'created_at', 'updated_at'])
                ? $request->sort_field
                : 'created_at';
        
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc'])
                ? strtolower($request->sort_order)
                : 'desc';
        
        $query->orderBy($sortField, $sortOrder);

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max(intval($request->per_page ?? 15), 1), 100);
            $users = $query->paginate($perPage)->withQueryString();
            return $this->paginatedResponse($users, UserResource::collection($users));
        } else {
            $users = $query->get();
            return $this->successResponse(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        }
    }

    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }


}