<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conference\ConferenceEditorStoreRequest;
use App\Http\Resources\Conference\ConferenceEditorResource;
use App\Http\Resources\User\UserResource;
use App\Models\Conference;
use App\Models\User;
use App\Services\ConferenceLockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConferenceEditorController extends Controller
{
    use ApiResponse;

    protected $lockService;

    /**
     * Constructor to inject ConferenceLockService
     */
    public function __construct(ConferenceLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Display a listing of editors attached to the conference.
     */
    public function index(Request $request, Conference $conference): JsonResponse
    {
        $query = $conference->editors()->with('university');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if ($request->has('university_id') && !empty($request->university_id)) {
            $query->where('users.university_id', $request->university_id);
        }
        
        // Sort options with consistent ordering
        $sortField = $request->sort_field;
        if (in_array($sortField, ['name', 'email', 'created_at', 'updated_at'])) {
            $sortField = "users.$sortField";
        } else {
            $sortField = 'users.name'; 
        }
        
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'asc';

        // Primary sort by the requested field
        $query->orderBy($sortField, $sortOrder);
        
        // Always add a secondary sort by 'users.id' to ensure consistent pagination
        $query->orderBy('users.id', $sortOrder);

        if (!$request->has('page') && !$request->has('per_page')) {
            $editors = $query->get();
            
            return $this->successResponse(
                ConferenceEditorResource::collection($editors),
                'Conference editors retrieved successfully'
            );
        }

        $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
        $editors = $query->paginate($perPage)->withQueryString();
        
        return $this->paginatedResponse(
            $editors, 
            ConferenceEditorResource::collection($editors),
            'Conference editors retrieved successfully'
        );
    }

    /**
     * Display a listing of editors not attached to the conference.
     */
    public function unattached(Request $request, Conference $conference): JsonResponse
    {
        $query = User::role('editor');

        $attachedEditorIds = $conference->editors()->pluck('users.id');
        $query->whereNotIn('id', $attachedEditorIds);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('university_id') && !empty($request->university_id)) {
            $query->where('university_id', $request->university_id);
        }

        // Sort options with consistent ordering
        $sortField = in_array($request->sort_field, ['name', 'email', 'created_at', 'updated_at']) 
            ? $request->sort_field 
            : 'name';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'asc';

        // Primary sort by the requested field
        $query->orderBy($sortField, $sortOrder);
        
        // Always add a secondary sort by 'id' to ensure consistent pagination
        $query->orderBy('id', $sortOrder);
        
        if (!$request->has('page') && !$request->has('per_page')) {
            $users = $query->get();
            $users->load('university');
            
            return $this->successResponse(
                UserResource::collection($users),
                'Available editors retrieved successfully'
            );
        }

        $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
        $users = $query->paginate($perPage)->withQueryString();
        $users->load('university');
        
        return $this->paginatedResponse(
            $users, 
            UserResource::collection($users),
            'Available editors retrieved successfully'
        );
    }

    /**
     * Attach an editor to the conference.
     */
    public function store(ConferenceEditorStoreRequest $request, Conference $conference): JsonResponse
    {
        $userId = $request->user_id;

        if ($conference->editors()->where('user_id', $userId)->exists()) {
            return $this->errorResponse('User is already an editor for this conference', 422);
        }
        
        $conference->editors()->attach($userId, [
            'assigned_by' => $request->user()->id
        ]);

        $editor = $conference->editors()
                    ->with('university')
                    ->where('user_id', $userId)
                    ->first();
        
        return $this->successResponse(
            new ConferenceEditorResource($editor),
            'Editor added successfully',
            201
        );
    }

    /**
     * Detach an editor from the conference and release their lock if held.
     */
    public function destroy(Conference $conference, User $editor): JsonResponse
    {
        if (!$conference->editors()->where('user_id', $editor->id)->exists()) {
            return $this->errorResponse('User is not an editor for this conference', 422);
        }

        $conference->editors()->detach($editor->id);
        
        $this->lockService->releaseLock($conference->id, $editor->id);
    
        return $this->successResponse(null, 'Editor removed successfully');
    }
}