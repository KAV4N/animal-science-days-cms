<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conference\ConferenceStoreRequest;
use App\Http\Requests\Conference\ConferenceUpdateRequest;
use App\Http\Requests\Conference\ConferenceEditorStoreRequest;

use App\Http\Resources\Conference\ConferenceResource;
use App\Http\Resources\Conference\ConferenceCollection;
use App\Http\Resources\User\UserResource;

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

        $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
        $conferences = $query->paginate($perPage)->withQueryString();
        $conferences->load('university', 'editors');
        return $this->paginatedResponse($conferences, ConferenceResource::collection($conferences));
    }

    public function store(ConferenceStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;

        $conference = Conference::create($validated);

        return $this->successResponse(
            new ConferenceResource($conference->load('university', 'editors')),
            'Conference created successfully',
            201
        );

    }

    public function show(Conference $conference): JsonResponse
    {
        $conference->load('university', 'editors');

        return $this->successResponse(
            new ConferenceResource($conference),
            'Conference retrieved successfully'
        );
    }

    public function update(ConferenceUpdateRequest $request, Conference $conference): JsonResponse
    {
        $conference->update($request->validated());

        return $this->successResponse(
            new ConferenceResource($conference->fresh(['university', 'editors'])),
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
            new ConferenceResource($conference->fresh()),
            $message
        );
    }
    
    public function getEditors(Conference $conference): JsonResponse
    {
        $editors = $conference->editors()->with('university')->get();
    
        return $this->successResponse(
            UserResource::collection($editors),
            'Conference editors retrieved successfully'
        );
    }
    
    public function attachEditor(ConferenceEditorStoreRequest $request, Conference $conference): JsonResponse
    {
    
        $conference->editors()->attach($request->user_id, [
            'assigned_by' => $request->user()->id,
            'assigned_at' => now()
        ]);
    
        return $this->successResponse(
            new ConferenceEditorResource($editor->load('user', 'assignedByUser')),
            'Editor added successfully',
            201
        );
    }
    
    public function detachEditor(Conference $conference, User $user): JsonResponse
    {
        $conference->editors()->detach($user->id);
    
        return $this->successResponse(null, 'Editor removed successfully');
    }


}