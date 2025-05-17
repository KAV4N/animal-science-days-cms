<?php

namespace App\Http\Middleware;

use App\Models\Conference;
use App\Services\ConferenceLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckConferenceLock
{
    /**
     * @var ConferenceLockService
     */
    protected $lockService;

    public function __construct(ConferenceLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check for PUT/PATCH/DELETE methods that modify conference data
        if (!in_array($request->method(), ['PUT', 'PATCH', 'DELETE', 'POST'])) {
            return $next($request);
        }
        
        // Get conference from route
        $conference = $request->route('conference');
        
        if (!$conference instanceof Conference) {
            return $next($request);
        }
        
        $user = $request->user();
        $lockInfo = $this->lockService->checkLock($conference->id);
        
        // If no lock or user owns the lock, proceed
        if (!$lockInfo || $lockInfo['user_id'] === $user->id) {
            // Automatically refresh the lock
            if ($lockInfo && $lockInfo['user_id'] === $user->id) {
                $this->lockService->refreshLock($conference->id, $user->id);
            }
            
            return $next($request);
        }
        
        // Otherwise, reject the request with lock information
        return response()->json([
            'success' => false,
            'message' => 'Conference is currently being edited by another user',
            'errors' => [
                'lock_info' => $lockInfo
            ]
        ], Response::HTTP_LOCKED); // 423 Locked
    }
}