<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageMenu\StorePageMenuRequest;
use App\Http\Requests\PageMenu\UpdatePageMenuRequest;
use App\Http\Requests\PageMenu\UpdatePositionRequest;
use App\Http\Resources\PageMenu\PageMenuResource;
use App\Models\Conference;
use App\Models\PageMenu;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->orderBy('order', 'asc')  // Changed from created_at to order
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
        
        // Set the order to be the last in the list
        $lastOrder = $conference->pageMenus()->max('order') ?? 0;
        $validated['order'] = $lastOrder + 1;

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
    
    /**
     * Update the position of the specified resource.
     *
     * @param  \App\Http\Requests\PageMenu\UpdatePositionRequest  $request
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePosition(UpdatePositionRequest $request, Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        $newPosition = $request->position;
        $currentPosition = $menu->order;

        // Begin a database transaction to ensure all operations complete successfully
        DB::beginTransaction();
        
        try {
            if ($newPosition < $currentPosition) {
                // Moving up: increment positions of items between new and current positions
                $conference->pageMenus()
                    ->where('order', '>=', $newPosition)
                    ->where('order', '<', $currentPosition)
                    ->increment('order');
            } else if ($newPosition > $currentPosition) {
                // Moving down: decrement positions of items between current and new positions
                $conference->pageMenus()
                    ->where('order', '>', $currentPosition)
                    ->where('order', '<=', $newPosition)
                    ->decrement('order');
            } else {
                // No change needed
                DB::commit();
                return $this->successResponse(
                    new PageMenuResource($menu),
                    'Position unchanged'
                );
            }

            // Update the position of the target item
            $menu->order = $newPosition;
            $menu->updated_by = auth()->id();
            $menu->save();

            DB::commit();

            // Fetch all items in their new order for the response
            $orderedMenus = $conference->pageMenus()
                ->orderBy('order', 'asc')
                ->get();

            return $this->successResponse(
                PageMenuResource::collection($orderedMenus),
                'Position updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update position: ' . $e->getMessage(), 500);
        }
    }
}