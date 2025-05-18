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
     * Create a new middleware instance.
     */
    public function __construct(
        protected ConferenceLockService $lockService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['PUT', 'PATCH', 'DELETE', 'POST'])) {
            return $next($request);
        }

        $conference = $request->route('conference');
        if (!$conference instanceof Conference) {
            return $next($request);
        }
        $user = $request->user();
                
        $lockInfo = $this->lockService->checkLock($conference->id);
        if (!$lockInfo) {
            return response()->json([
                'success' => false,
                'message' => 'You must acquire a lock on this conference before making changes',
            ], Response::HTTP_LOCKED); 
        }
        
        if ($lockInfo['user_id'] !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Conference is currently being edited by another user',
                'errors' => [
                    'lock_info' => $lockInfo
                ]
            ], Response::HTTP_LOCKED); 
        }
        
        $this->lockService->refreshLock($conference->id, $user->id);
        
        return $next($request);
    }
}