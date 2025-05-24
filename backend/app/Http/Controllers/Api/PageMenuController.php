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
            ->orderBy('order', 'asc')
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
        
        // If order is not specified or is null, place it at the end
        if (!isset($validated['order']) || $validated['order'] === null) {
            $lastOrder = $conference->pageMenus()->max('order') ?? 0;
            $validated['order'] = $lastOrder + 1;
        } else {
            // If order is specified, make room for the new item and adjust existing orders
            DB::beginTransaction();
            try {
                // Increment order of all items that have order >= specified order
                $conference->pageMenus()
                    ->where('order', '>=', $validated['order'])
                    ->increment('order');
                    
                $menu = PageMenu::create($validated);
                DB::commit();
                
                return $this->successResponse(
                    new PageMenuResource($menu),
                    'Page menu created successfully',
                    201
                );
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->errorResponse('Failed to create menu: ' . $e->getMessage(), 500);
            }
        }

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

        // Handle order update if specified
        if (isset($validated['order']) && $validated['order'] !== null && $validated['order'] !== $menu->order) {
            DB::beginTransaction();
            try {
                $oldOrder = $menu->order;
                $newOrder = $validated['order'];

                if ($newOrder > $oldOrder) {
                    // Moving down: decrement order of items between old and new position
                    $conference->pageMenus()
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->where('id', '!=', $menu->id)
                        ->decrement('order');
                } else {
                    // Moving up: increment order of items between new and old position
                    $conference->pageMenus()
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->where('id', '!=', $menu->id)
                        ->increment('order');
                }

                $menu->update($validated);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->errorResponse('Failed to update menu: ' . $e->getMessage(), 500);
            }
        } else {
            $menu->update($validated);
        }

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

        DB::beginTransaction();
        
        try {
            $currentOrder = $menu->order;
            
            // Delete the menu
            $menu->delete();
            
            // Reorder remaining menus to fill the gap
            $conference->pageMenus()
                ->where('order', '>', $currentOrder)
                ->decrement('order');
            
            DB::commit();

            return $this->successResponse(
                null,
                'Page menu deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete menu: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update the position of the specified resource - Simple up/down movement.
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

        $direction = $request->input('direction'); // 'up' or 'down'
        $currentOrder = $menu->order;

        DB::beginTransaction();
        
        try {
            if ($direction === 'up') {
                // Find the menu item directly above (lower order number)
                $targetMenu = $conference->pageMenus()
                    ->where('order', '<', $currentOrder)
                    ->orderBy('order', 'desc')
                    ->first();

                if (!$targetMenu) {
                    DB::rollBack();
                    return $this->errorResponse('Cannot move menu up - already at the top', 400);
                }

                // Swap positions
                $targetOrder = $targetMenu->order;
                $targetMenu->update(['order' => $currentOrder, 'updated_by' => auth()->id()]);
                $menu->update(['order' => $targetOrder, 'updated_by' => auth()->id()]);

            } elseif ($direction === 'down') {
                // Find the menu item directly below (higher order number)
                $targetMenu = $conference->pageMenus()
                    ->where('order', '>', $currentOrder)
                    ->orderBy('order', 'asc')
                    ->first();

                if (!$targetMenu) {
                    DB::rollBack();
                    return $this->errorResponse('Cannot move menu down - already at the bottom', 400);
                }

                // Swap positions
                $targetOrder = $targetMenu->order;
                $targetMenu->update(['order' => $currentOrder, 'updated_by' => auth()->id()]);
                $menu->update(['order' => $targetOrder, 'updated_by' => auth()->id()]);

            } else {
                DB::rollBack();
                return $this->errorResponse('Invalid direction. Use "up" or "down"', 400);
            }

            DB::commit();

            // Fetch all menus in their new order for the response
            $orderedMenus = $conference->pageMenus()
                ->orderBy('order', 'asc')
                ->get();

            return $this->successResponse(
                PageMenuResource::collection($orderedMenus),
                'Menu position updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update position: ' . $e->getMessage(), 500);
        }
    }
}