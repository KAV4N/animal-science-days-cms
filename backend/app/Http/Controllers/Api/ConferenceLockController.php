<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conference\ConferenceResource;
use App\Models\Conference;
use App\Services\ConferenceLockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ConferenceLockController extends Controller
{
    use ApiResponse;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ConferenceLockService $lockService
    ) {}

    /**
     * Acquire a lock on a conference.
     */
    public function acquireLock(Request $request, Conference $conference): JsonResponse
    {
        if (Gate::denies('acquireLock', $conference)) {
            return $this->errorResponse('You are not authorized to lock this conference', Response::HTTP_FORBIDDEN);
        }
        
        $user = $request->user();
        $result = $this->lockService->acquireLock($conference, $user);
        
        if ($result === true) {
            return $this->successResponse(
                new ConferenceResource($conference->fresh(['university', 'editors.university'])),
                'Conference lock acquired successfully'
            );
        }
        
        return $this->errorResponse(
            'Conference is currently being edited by another user',
            Response::HTTP_LOCKED,
            ['lock_info' => $result]
        );
    }
    
    /**
     * Release a lock on a conference.
     */
    public function releaseLock(Request $request, Conference $conference): JsonResponse
    {
        $user = $request->user();
        $result = $this->lockService->releaseLock($conference->id, $user->id);
        
        if ($result) {
            return $this->successResponse(
                new ConferenceResource($conference->fresh(['university', 'editors.university'])),
                'Conference lock released successfully'
            );
        }
        
        return $this->errorResponse(
            'You do not own the lock on this conference',
            Response::HTTP_FORBIDDEN
        );
    }
    
    /**
     * Check if a conference is locked.
     */
    public function checkLock(Conference $conference): JsonResponse
    {
        $lockInfo = $this->lockService->checkLock($conference->id);
        
        if ($lockInfo) {
            return $this->successResponse(
                [
                    'is_locked' => true,
                    'lock_info' => $lockInfo,
                ],
                'Conference is currently locked'
            );
        }
        
        return $this->successResponse(
            ['is_locked' => false],
            'Conference is not locked'
        );
    }
    
    /**
     * Refresh a lock on a conference.
     */
    public function refreshLock(Request $request, Conference $conference): JsonResponse
    {
        $user = $request->user();
        $result = $this->lockService->refreshLock($conference->id, $user->id);
        
        if ($result) {
            return $this->successResponse(
                new ConferenceResource($conference->fresh(['university', 'editors.university'])),
                'Conference lock refreshed successfully'
            );
        }
        
        return $this->errorResponse(
            'You do not own the lock on this conference',
            Response::HTTP_FORBIDDEN
        );
    }
    
    /**
     * Force release a lock on a conference (admin only).
     */
    public function forceReleaseLock(Conference $conference): JsonResponse
    {
        if (Gate::denies('forceReleaseLock', $conference)) {
            return $this->errorResponse('You are not authorized to force release this lock', Response::HTTP_FORBIDDEN);
        }
        
        $this->lockService->forceReleaseLock($conference->id);
        
        return $this->successResponse(
            null,
            'Conference lock forcibly released'
        );
    }
}