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

class ConferenceLockController extends Controller
{
    use ApiResponse;

    protected $lockService;

    public function __construct(ConferenceLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public function acquireLock(Request $request, Conference $conference): JsonResponse
    {
        if (!Gate::allows('acquireLock', $conference)) {
            return $this->errorResponse('You are not authorized to lock this conference', 403);
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
            423,
            ['lock_info' => $result]
        );
    }
    
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
            403
        );
    }
    
    public function checkLock(Request $request, Conference $conference): JsonResponse
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
            403
        );
    }
    
    public function forceReleaseLock(Request $request, Conference $conference): JsonResponse
    {
        if (!Gate::allows('forceReleaseLock', $conference)) {
            return $this->errorResponse('You are not authorized to force release this lock', 403);
        }
        
        $this->lockService->forceReleaseLock($conference->id);
        
        return $this->successResponse(
            null,
            'Conference lock forcibly released'
        );
    }
}