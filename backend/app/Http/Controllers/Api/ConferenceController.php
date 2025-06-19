<?php

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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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

        // Apply sorting with proper handling for university sorting
        $sortField = $request->sort_field;
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';

        // Handle university sorting by joining with universities table
        if ($sortField === 'university') {
            $query->join('universities', 'conferences.university_id', '=', 'universities.id')
                  ->select('conferences.*')
                  ->orderBy('universities.full_name', $sortOrder);
        } else {
            // Regular sorting for other fields
            $allowedSortFields = ['name', 'start_date', 'end_date', 'created_at', 'updated_at'];
            $sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'created_at';
            $query->orderBy($sortField, $sortOrder);
        }

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
        try {
            $validated = $request->validated();
            $validated['created_by'] = $request->user()->id;
            
            // Handle latest conference logic
            if (isset($validated['is_latest']) && $validated['is_latest']) {
                $this->ensureOnlyOneLatestConference(null);
            }
            
            $conference = Conference::create($validated);

            return $this->successResponse(
                new ConferenceResource($conference->load(['university'])),
                'Conference created successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error('Failed to create conference', [
                'error' => $e->getMessage(),
                'data' => $validated ?? null
            ]);
            
            return $this->errorResponse('Failed to create conference: ' . $e->getMessage(), 500);
        }
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
        
        try {
            $validated = $request->validated();
            
            // Handle latest conference logic
            if (isset($validated['is_latest']) && $validated['is_latest']) {
                $this->ensureOnlyOneLatestConference($conference->id);
            }
            
            $conference->update($validated);
            
            $lockInfo = $this->lockService->checkLock($conference->id);
            if ($lockInfo && $lockInfo['user_id'] === $request->user()->id) {
                $this->lockService->refreshLock($conference->id, $request->user()->id);
            }

            return $this->successResponse(
                new ConferenceResource($conference->fresh(['university'])),
                'Conference updated successfully'
            );
        } catch (\Exception $e) {
            Log::error('Failed to update conference', [
                'conference_id' => $conference->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to update conference: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a conference and all associated media files
     */
    public function destroy(Conference $conference): JsonResponse
    {
        if (!Gate::allows('delete', $conference)) {
            return $this->errorResponse('You are not authorized to delete this conference', 403);
        }
        
        try {
            // Force release any locks on this conference
            $this->lockService->forceReleaseLock($conference->id);
            
            // Get all media files associated with this conference
            $mediaItems = $conference->media;
            
            Log::info('Deleting conference and associated media', [
                'conference_id' => $conference->id,
                'conference_name' => $conference->name,
                'media_count' => $mediaItems->count()
            ]);
            
            // Delete all media files (both database records and physical files)
            foreach ($mediaItems as $media) {
                try {
                    // This will delete both the database record and the physical file
                    // Thanks to Spatie Media Library's built-in cleanup
                    $media->delete();
                    
                    Log::info('Media file deleted', [
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'conference_id' => $conference->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to delete media file', [
                        'media_id' => $media->id,
                        'file_name' => $media->file_name,
                        'conference_id' => $conference->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other files even if one fails
                }
            }
            
            // Delete the conference itself
            $conference->delete();
            
            Log::info('Conference deleted successfully', [
                'conference_id' => $conference->id,
                'conference_name' => $conference->name
            ]);

            return $this->successResponse(null, 'Conference and all associated files deleted successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete conference', [
                'conference_id' => $conference->id,
                'conference_name' => $conference->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Failed to delete conference: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update conference status (published, latest)
     */
    public function updateStatus(Request $request, Conference $conference): JsonResponse
    {
        if (!Gate::allows('update', $conference)) {
            return $this->errorResponse('You are not authorized to update this conference', 403);
        }
        
        try {
            $updates = [];
            $message = 'Conference status updated successfully';

            if ($request->has('latest')) {
                $isLatest = filter_var($request->latest, FILTER_VALIDATE_BOOLEAN);
                $updates['is_latest'] = $isLatest;
                $message = $isLatest ? 'Conference set as latest successfully' : 'Conference removed from latest status';
                
                // Handle latest conference logic
                if ($isLatest) {
                    $this->ensureOnlyOneLatestConference($conference->id);
                }
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
        } catch (\Exception $e) {
            Log::error('Failed to update conference status', [
                'conference_id' => $conference->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to update conference status: ' . $e->getMessage(), 500);
        }
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

    /**
     * Ensure only one conference can be marked as latest
     * 
     * @param int|null $excludeId Conference ID to exclude from the update (the one being set as latest)
     * @throws \Exception
     */
    private function ensureOnlyOneLatestConference(?int $excludeId = null): void
    {
        $query = Conference::where('is_latest', true);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $updated = $query->update(['is_latest' => false]);
        
        Log::info('Updated latest conference status', [
            'excluded_id' => $excludeId,
            'conferences_updated' => $updated
        ]);
    }
}