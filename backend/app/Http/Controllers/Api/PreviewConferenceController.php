<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conference\ConferenceResource;
use App\Http\Resources\PageMenu\PageMenuResource;
use App\Models\Conference;
use App\Models\PageMenu;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreviewConferenceController extends Controller
{
    use ApiResponse;

    /**
     * Get conference for preview (includes unpublished conferences for authorized users)
     */
    public function show(string $slug): JsonResponse
    {
        $user = auth()->user();
        
        $query = Conference::where('slug', $slug)
            ->with(['university']);

        // Check authorization for unpublished conferences
        if (!$this->canPreviewConference($user, $slug)) {
            $query->where('is_published', true);
        }

        $conference = $query->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found', 404);
        }

        return $this->successResponse(
            new ConferenceResource($conference),
            'Conference retrieved successfully'
        );
    }

    /**
     * Get pages for conference preview (includes unpublished pages for authorized users)
     */
    public function pages(string $conferenceSlug): JsonResponse
    {
        $user = auth()->user();
        
        $conferenceQuery = Conference::where('slug', $conferenceSlug);
        
        // Check authorization for unpublished conferences
        if (!$this->canPreviewConference($user, $conferenceSlug)) {
            $conferenceQuery->where('is_published', true);
        }

        $conference = $conferenceQuery->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found', 404);
        }

        $menuQuery = $conference->pageMenus()->orderBy('order', 'asc');
        
        // Include unpublished pages for authorized users
        if (!$this->canPreviewConference($user, $conferenceSlug)) {
            $menuQuery->where('is_published', true);
        }

        $menus = $menuQuery->get();
        
        return $this->successResponse(
            PageMenuResource::collection($menus),
            'Pages retrieved successfully'
        );
    }

    /**
     * Get specific page with data for preview
     */
    public function page(string $conferenceSlug, string $pageSlug): JsonResponse
    {
        $user = auth()->user();
        
        $conferenceQuery = Conference::where('slug', $conferenceSlug);
        
        // Check authorization for unpublished conferences
        if (!$this->canPreviewConference($user, $conferenceSlug)) {
            $conferenceQuery->where('is_published', true);
        }

        $conference = $conferenceQuery->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found', 404);
        }

        $menuQuery = $conference->pageMenus()
            ->where('slug', $pageSlug);

        // Include unpublished pages for authorized users
        if (!$this->canPreviewConference($user, $conferenceSlug)) {
            $menuQuery->where('is_published', true);
        }

        $menu = $menuQuery->with(['pageData' => function($query) use ($user, $conferenceSlug) {
            $query->orderBy('order', 'asc');
            // Include unpublished page data for authorized users
            if (!$this->canPreviewConference($user, $conferenceSlug)) {
                $query->where('is_published', true);
            }
        }])->first();

        if (!$menu) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse(
            new PageMenuResource($menu),
            'Page retrieved successfully'
        );
    }

    /**
     * Check if user can preview unpublished content for a conference
     */
    private function canPreviewConference($user, string $conferenceSlug): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin and admin can always preview
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Editor can preview conferences they are assigned to
        if ($user->hasRole('editor')) {
            $conference = Conference::where('slug', $conferenceSlug)->first();
            if ($conference) {
                return $conference->editors()->where('user_id', $user->id)->exists();
            }
        }

        return false;
    }
}