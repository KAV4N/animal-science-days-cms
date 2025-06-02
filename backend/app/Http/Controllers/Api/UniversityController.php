<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\University\UniversityStoreRequest;
use App\Http\Requests\University\UniversityUpdateRequest;
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

    public function store(UniversityStoreRequest $request): JsonResponse
    {
        $university = University::create($request->validated());

        return $this->successResponse(
            new UniversityResource($university),
            'University created successfully',
            201
        );
    }

    public function show(University $university): JsonResponse
    {
        return $this->successResponse(
            new UniversityResource($university),
            'University retrieved successfully'
        );
    }

    public function update(UniversityUpdateRequest $request, University $university): JsonResponse
    {
        $university->update($request->validated());

        return $this->successResponse(
            new UniversityResource($university->fresh()),
            'University updated successfully'
        );
    }

    public function destroy(University $university): JsonResponse
    {
        if ($university->users()->count() > 0 || $university->conferences()->count() > 0) {
            return $this->errorResponse('Cannot delete university with related records', 422);
        }

        $university->delete();

        return $this->successResponse(null, 'University deleted successfully');
    }
}