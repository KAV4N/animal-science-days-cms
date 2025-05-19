<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageMenu\StorePageMenuRequest;
use App\Http\Requests\PageMenu\UpdatePageMenuRequest;
use App\Http\Resources\PageMenu\PageMenuResource;
use App\Models\Conference;
use App\Models\PageMenu;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PageMenuController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Conference $conference)
    {
        $menus = $conference->pageMenus()
            ->when(request()->has('is_published'), function ($query) {
                return $query->where('is_published', request()->boolean('is_published'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate();

        return $this->paginatedResponse(
            $menus,
            PageMenuResource::collection($menus)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PageMenu\StorePageMenuRequest  $request
     * @param  \App\Models\Conference  $conference
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePageMenuRequest $request, Conference $conference)
    {
        $validated = $request->validated();
        $validated['conference_id'] = $conference->id;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $menu = PageMenu::create($validated);

        return $this->successResponse(
            new PageMenuResource($menu),
            'Page menu created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        return $this->successResponse(
            new PageMenuResource($menu->load('pageData'))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\PageMenu\UpdatePageMenuRequest  $request
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePageMenuRequest $request, Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $menu->update($validated);

        return $this->successResponse(
            new PageMenuResource($menu),
            'Page menu updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        // Check if the menu has associated data
        if ($menu->pageData()->count() > 0) {
            return $this->errorResponse('Cannot delete menu with associated data', 409);
        }

        $menu->delete();

        return $this->successResponse(
            null,
            'Page menu deleted successfully'
        );
    }
}