<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageMenu\PageMenuResource;
use App\Http\Resources\PageData\PageDataResource;
use App\Models\Conference;
use App\Models\PageMenu;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PublicPageMenuController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of published pages for a conference.
     *
     * @param  string  $conferenceSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($conferenceSlug)
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->firstOrFail();
        
        $menus = $conference->pageMenus()
            ->where('is_published', true)
            ->with(['pageData' => function($query) {
                $query->where('is_published', true)
                    ->orderBy('order', 'asc');
            }])
            ->orderBy('created_at', 'asc')
            ->get();
        
        return $this->successResponse(
            PageMenuResource::collection($menus),
            'Published pages retrieved successfully'
        );
    }

    /**
     * Display the specified menu with its published components.
     *
     * @param  string  $conferenceSlug
     * @param  string  $pageSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($conferenceSlug, $pageSlug)
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->firstOrFail();
        
        $menu = $conference->pageMenus()
            ->where('slug', $pageSlug)
            ->where('is_published', true)
            ->with(['pageData' => function($query) {
                $query->where('is_published', true)
                    ->orderBy('order', 'asc');
            }])
            ->firstOrFail();
        
        return $this->successResponse(
            new PageMenuResource($menu),
            'Page menu retrieved successfully'
        );
    }
}