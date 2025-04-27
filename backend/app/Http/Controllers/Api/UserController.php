<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }
}