<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Conference;
use App\Models\Media;
use Illuminate\Support\Facades\Gate;

class CheckMediaAccess
{
    /**
     * Handle an incoming request for media access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get conference and media from route parameters
        $conference = $request->route('conference');
        $mediaId = $request->route('mediaId');

        // If conference is not found
        if (!$conference instanceof Conference) {
            $conference = Conference::find($conference);
            if (!$conference) {
                return response()->json(['error' => 'Conference not found'], 404);
            }
        }

        // Find the media
        $media = $conference->media()->where('id', $mediaId)->first();
        if (!$media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Check access using the policy
        $user = auth()->user();
        
        // Use Gate to check the policy with optional user (for unauthenticated access)
        if (!Gate::forUser($user)->allows('serve', $media)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return $next($request);
    }
}