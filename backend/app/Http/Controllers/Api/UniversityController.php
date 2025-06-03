<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\University\UniversityResource;
use App\Models\University;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = University::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $universities = $query->get();

        return $this->successResponse(
            UniversityResource::collection($universities),
            'Universities retrieved successfully'
        );
    }

    public function show(University $university): JsonResponse
    {
        return $this->successResponse(
            new UniversityResource($university),
            'University retrieved successfully'
        );
    }
}