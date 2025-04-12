<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AccessController extends Controller
{
    /**
     * Access for editor role.
     */
    public function editor(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Editor access granted',
            'data' => [
                'access.editor' => 'accessed'
            ]
        ], 200);
    }

    /**
     * Access for admin role.
     */
    public function admin(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Admin access granted',
            'data' => [
                'access.admin' => 'accessed'
            ]
        ], 200);
    }

    /**
     * Access for super admin role.
     */
    public function superAdmin(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Super Admin access granted',
            'data' => [
                'access.super_admin' => 'accessed'
            ]
        ], 200);
    }
}