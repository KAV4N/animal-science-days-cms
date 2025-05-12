<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conference\ConferenceStoreRequest;
use App\Http\Requests\Conference\ConferenceUpdateRequest;

use App\Http\Resources\Conference\ConferenceResource;

use App\Models\Conference;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConferenceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Conference::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('university_id') && !empty($request->university_id)) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_latest')) {
            $query->where('is_latest', filter_var($request->is_latest, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('start_date_after')) {
            $query->where('start_date', '>=', $request->start_date_after);
        }

        if ($request->has('end_date_before')) {
            $query->where('end_date', '<=', $request->end_date_before);
        }


        $sortField = in_array($request->sort_field, ['name', 'title', 'start_date', 'end_date', 'created_at', 'updated_at']) 
            ? $request->sort_field 
            : 'created_at';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';
        $query->orderBy($sortField, $sortOrder);

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
            $conferences = $query->paginate($perPage)->withQueryString();
            $conferences->load(['university', 'editors.university']);
            return $this->paginatedResponse($conferences, ConferenceResource::collection($conferences));
        } else {
            $count = $query->count();
            
            $conferences = $query->get();
            $conferences->load(['university', 'editors.university']);
            return $this->successResponse(
                ConferenceResource::collection($conferences),
                'Conferences retrieved successfully'
            );
        }
    }

    public function store(ConferenceStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;

        $conference = Conference::create($validated);

        return $this->successResponse(
            new ConferenceResource($conference->load(['university', 'editors.university'])),
            'Conference created successfully',
            201
        );
    }

    public function show(Conference $conference): JsonResponse
    {
        $conference->load(['university', 'editors.university']);

        return $this->successResponse(
            new ConferenceResource($conference),
            'Conference retrieved successfully'
        );
    }

    public function update(ConferenceUpdateRequest $request, Conference $conference): JsonResponse
    {
        $conference->update($request->validated());

        return $this->successResponse(
            new ConferenceResource($conference->fresh(['university', 'editors.university'])),
            'Conference updated successfully'
        );
    }

    public function destroy(Conference $conference): JsonResponse
    {
        $conference->delete();

        return $this->successResponse(null, 'Conference deleted successfully');
    }

    public function updateStatus(Request $request, Conference $conference): JsonResponse
    {
        $updates = [];
        $message = 'Conference status updated successfully';

        if ($request->has('published')) {
            $isPublished = filter_var($request->published, FILTER_VALIDATE_BOOLEAN);
            $updates['is_published'] = $isPublished;
            $message = $isPublished ? 'Conference published successfully' : 'Conference unpublished successfully';
        }
        if ($request->has('latest')) {
            $isLatest = filter_var($request->latest, FILTER_VALIDATE_BOOLEAN);
            $updates['is_latest'] = $isLatest;
            $message = $isLatest ? 'Conference set as latest successfully' : 'Conference removed from latest status';
        }
        if (!empty($updates)) {
            $conference->update($updates);
        }

        return $this->successResponse(
            new ConferenceResource($conference->fresh(['university', 'editors.university'])),
            $message
        );
    }

    public function latest(Request $request): JsonResponse
    {
        $conference = Conference::where('is_latest', true)->first();
    
        if (!$conference) {
            return $this->errorResponse('No latest conference found', 404);
        }
    
        $conference->load(['university', 'editors.university']);
    
        return $this->successResponse(
            new ConferenceResource($conference),
            'Latest conference retrieved successfully'
        );
    }


    public function myConferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Conference::query();

        $query->where('created_by', $user->id)
              ->orWhereHas('editors', function ($q) use ($user) {
                  $q->where('users.id', $user->id);
              });

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_published')) {
            $query->where('is_published', filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN));
        }

        $sortField = in_array($request->sort_field, ['name', 'title', 'start_date', 'end_date', 'created_at', 'updated_at']) 
            ? $request->sort_field 
            : 'created_at';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';
        $query->orderBy($sortField, $sortOrder);

        $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
        $conferences = $query->paginate($perPage)->withQueryString();
        $conferences->load(['university', 'editors.university']);

        return $this->paginatedResponse(
            $conferences, 
            ConferenceResource::collection($conferences),
            'User conferences retrieved successfully'
        );
    }

}