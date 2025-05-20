<?php
// File: app/Http/Controllers/Api/ConferenceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conference\ConferenceStoreRequest;
use App\Http\Requests\Conference\ConferenceUpdateRequest;
use App\Http\Resources\Conference\ConferenceResource;
use App\Models\Conference;
use App\Services\ConferenceLockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ConferenceController extends Controller
{
    use ApiResponse;
    
    protected $lockService;
    
    public function __construct(ConferenceLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Get all conferences for authenticated users with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Conference::query();

        // Apply filters
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

        // Apply sorting
        $sortField = in_array($request->sort_field, ['name', 'title', 'start_date', 'end_date', 'created_at', 'updated_at']) 
            ? $request->sort_field 
            : 'created_at';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';
        $query->orderBy($sortField, $sortOrder);

        // Handle pagination or return all results
        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
            $conferences = $query->paginate($perPage)->withQueryString();
            $conferences->load(['university']);
            
            $conferences->each(function ($conference) {
                $conference->lock_status = $this->lockService->checkLock($conference->id);
            });
            
            return $this->paginatedResponse($conferences, ConferenceResource::collection($conferences));
        } else {
            $conferences = $query->get();
            $conferences->load(['university']);
            
            $conferences->each(function ($conference) {
                $conference->lock_status = $this->lockService->checkLock($conference->id);
            });
            
            return $this->successResponse(
                ConferenceResource::collection($conferences),
                'Conferences retrieved successfully'
            );
        }
    }

    /**
     * Create a new conference
     */
    public function store(ConferenceStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;
        
        // Check if trying to create a conference set as latest
        if (isset($validated['is_latest']) && $validated['is_latest']) {
            // Check if the current latest conference is locked by another user
            $latestConference = Conference::where('is_latest', true)->first();
            if ($latestConference) {
                $latestLockInfo = $this->lockService->checkLock($latestConference->id);
                if ($latestLockInfo && $latestLockInfo['user_id'] !== $request->user()->id) {
                    return $this->errorResponse(
                        'Cannot create conference as latest: Current latest conference is being edited by another user',
                        423,
                        ['lock_info' => $latestLockInfo]
                    );
                }
            }
        }

        $conference = Conference::create($validated);

        return $this->successResponse(
            new ConferenceResource($conference->load(['university'])),
            'Conference created successfully',
            201
        );
    }

    /**
     * Get a specific conference for authenticated users
     */
    public function show(Conference $conference): JsonResponse
    {
        if (!Gate::allows('view', $conference)) {
            return $this->errorResponse('You are not authorized to view this conference', 403);
        }
        
        $conference->load(['university']);
        
        $conference->lock_status = $this->lockService->checkLock($conference->id);

        return $this->successResponse(
            new ConferenceResource($conference),
            'Conference retrieved successfully'
        );
    }

    /**
     * Update a conference
     */
    public function update(ConferenceUpdateRequest $request, Conference $conference): JsonResponse
    {
        if (!Gate::allows('update', $conference)) {
            return $this->errorResponse('You are not authorized to update this conference', 403);
        }
        
        $lockInfo = $this->lockService->checkLock($conference->id);
        if ($lockInfo && $lockInfo['user_id'] !== $request->user()->id) {
            return $this->errorResponse(
                'Conference is currently being edited by another user',
                423,
                ['lock_info' => $lockInfo]
            );
        }
        
        $validated = $request->validated();
        
        // Check if trying to update a conference to be set as latest
        if (isset($validated['is_latest']) && $validated['is_latest'] && !$conference->is_latest) {
            // Check if the current latest conference is locked by another user
            $latestConference = Conference::where('is_latest', true)
                ->where('id', '!=', $conference->id)
                ->first();
                
            if ($latestConference) {
                $latestLockInfo = $this->lockService->checkLock($latestConference->id);
                if ($latestLockInfo && $latestLockInfo['user_id'] !== $request->user()->id) {
                    return $this->errorResponse(
                        'Cannot set as latest: Current latest conference is being edited by another user',
                        423,
                        ['lock_info' => $latestLockInfo]
                    );
                }
            }
        }
        
        $conference->update($validated);
        
        if ($lockInfo && $lockInfo['user_id'] === $request->user()->id) {
            $this->lockService->refreshLock($conference->id, $request->user()->id);
        }

        return $this->successResponse(
            new ConferenceResource($conference->fresh(['university'])),
            'Conference updated successfully'
        );
    }

    /**
     * Delete a conference
     */
    public function destroy(Conference $conference): JsonResponse
    {
        if (!Gate::allows('delete', $conference)) {
            return $this->errorResponse('You are not authorized to delete this conference', 403);
        }
        
        $this->lockService->forceReleaseLock($conference->id);
        
        $conference->delete();

        return $this->successResponse(null, 'Conference deleted successfully');
    }

    /**
     * Update conference status (published, latest)
     */
    public function updateStatus(Request $request, Conference $conference): JsonResponse
    {
        if (!Gate::allows('update', $conference)) {
            return $this->errorResponse('You are not authorized to update this conference', 403);
        }
        
        $updates = [];
        $message = 'Conference status updated successfully';

        if ($request->has('latest')) {
            $isLatest = filter_var($request->latest, FILTER_VALIDATE_BOOLEAN);
            
            // If trying to set as latest, check if the conference is locked by another user
            if ($isLatest) {
                // Check if this conference is locked by another user
                $lockInfo = $this->lockService->checkLock($conference->id);
                if ($lockInfo && $lockInfo['user_id'] !== $request->user()->id) {
                    return $this->errorResponse(
                        'Cannot set as latest: Conference is currently being edited by another user',
                        423,
                        ['lock_info' => $lockInfo]
                    );
                }
                
                // Check if the current latest conference is locked by another user
                $latestConference = Conference::where('is_latest', true)
                    ->where('id', '!=', $conference->id)
                    ->first();
                    
                if ($latestConference) {
                    $latestLockInfo = $this->lockService->checkLock($latestConference->id);
                    if ($latestLockInfo && $latestLockInfo['user_id'] !== $request->user()->id) {
                        return $this->errorResponse(
                            'Cannot set as latest: Current latest conference is being edited by another user',
                            423,
                            ['lock_info' => $latestLockInfo]
                        );
                    }
                }
            }
            
            $updates['is_latest'] = $isLatest;
            $message = $isLatest ? 'Conference set as latest successfully' : 'Conference removed from latest status';
        }
        
        if ($request->has('published')) {
            $isPublished = filter_var($request->published, FILTER_VALIDATE_BOOLEAN);
            $updates['is_published'] = $isPublished;
            $message = $isPublished ? 'Conference published successfully' : 'Conference unpublished successfully';
        }
        
        if (!empty($updates)) {
            $conference->update($updates);
        }

        $lockInfo = $this->lockService->checkLock($conference->id);
        if ($lockInfo && $lockInfo['user_id'] === $request->user()->id) {
            $this->lockService->refreshLock($conference->id, $request->user()->id);
        }

        return $this->successResponse(
            new ConferenceResource($conference->fresh(['university'])),
            $message
        );
    }

    /**
     * Get the latest conference
     */
    public function latest(Request $request): JsonResponse
    {
        $conference = Conference::where('is_latest', true)->first();
    
        if (!$conference) {
            return $this->errorResponse('No latest conference found', 404);
        }
    
        $conference->load(['university']);
        
        $conference->lock_status = $this->lockService->checkLock($conference->id);
    
        return $this->successResponse(
            new ConferenceResource($conference),
            'Latest conference retrieved successfully'
        );
    }

    /**
     * Get conferences where the current user is an editor or creator
     */
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
        $conferences->load(['university']);
        
        $conferences->each(function ($conference) {
            $conference->lock_status = $this->lockService->checkLock($conference->id);
        });

        return $this->paginatedResponse(
            $conferences, 
            ConferenceResource::collection($conferences),
            'User conferences retrieved successfully'
        );
    }
}