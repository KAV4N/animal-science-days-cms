<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageData\StorePageDataRequest;
use App\Http\Requests\PageData\UpdatePageDataRequest;
use App\Http\Requests\PageData\UpdatePositionRequest;
use App\Http\Resources\PageData\PageDataResource;
use App\Models\Conference;
use App\Models\PageMenu;
use App\Models\PageData;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PageDataController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        $pageData = $menu->pageData()
            ->when(request()->has('is_published'), function ($query) {
                return $query->where('is_published', request()->boolean('is_published'));
            })
            ->when(request()->has('component_type'), function ($query) {
                return $query->where('component_type', request()->get('component_type'));
            })
            ->when(request()->has('tag'), function ($query) {
                return $query->where('tag', request()->get('tag'));
            })
            ->orderBy('order', 'asc')
            ->paginate();

        return $this->paginatedResponse(
            $pageData,
            PageDataResource::collection($pageData)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PageData\StorePageDataRequest  $request
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePageDataRequest $request, Conference $conference, PageMenu $menu)
    {
        if ($menu->conference_id !== $conference->id) {
            return $this->errorResponse('Menu does not belong to this conference', 404);
        }

        $validated = $request->validated();
        $validated['menu_id'] = $menu->id;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        // If order is not specified or is null, place it at the end
        if (!isset($validated['order']) || $validated['order'] === null) {
            $lastOrder = $menu->pageData()->max('order') ?? 0;
            $validated['order'] = $lastOrder + 1;
        } else {
            try {
                // If order is specified, make room for the new item and adjust existing orders
                $menu->pageData()
                    ->where('order', '>=', $validated['order'])
                    ->increment('order');
                    
                $pageData = PageData::create($validated);
                
                return $this->successResponse(
                    new PageDataResource($pageData),
                    'Page component created successfully',
                    201
                );
            } catch (\Exception $e) {
                return $this->errorResponse('Failed to create component: ' . $e->getMessage(), 500);
            }
        }

        $pageData = PageData::create($validated);

        return $this->successResponse(
            new PageDataResource($pageData),
            'Page component created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @param  \App\Models\PageData  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Conference $conference, PageMenu $menu, PageData $data)
    {
        if ($menu->conference_id !== $conference->id || $data->menu_id !== $menu->id) {
            return $this->errorResponse('Resource not found', 404);
        }

        return $this->successResponse(
            new PageDataResource($data)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\PageData\UpdatePageDataRequest  $request
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @param  \App\Models\PageData  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePageDataRequest $request, Conference $conference, PageMenu $menu, PageData $data)
    {
        if ($menu->conference_id !== $conference->id || $data->menu_id !== $menu->id) {
            return $this->errorResponse('Resource not found', 404);
        }

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        // Handle order update if specified
        if (isset($validated['order']) && $validated['order'] !== null && $validated['order'] !== $data->order) {
            try {
                $oldOrder = $data->order;
                $newOrder = $validated['order'];

                if ($newOrder > $oldOrder) {
                    // Moving down: decrement order of items between old and new position
                    $menu->pageData()
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->where('id', '!=', $data->id)
                        ->decrement('order');
                } else {
                    // Moving up: increment order of items between new and old position
                    $menu->pageData()
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->where('id', '!=', $data->id)
                        ->increment('order');
                }

                $data->update($validated);
            } catch (\Exception $e) {
                return $this->errorResponse('Failed to update component: ' . $e->getMessage(), 500);
            }
        } else {
            $data->update($validated);
        }

        return $this->successResponse(
            new PageDataResource($data),
            'Page component updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @param  \App\Models\PageData  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Conference $conference, PageMenu $menu, PageData $data)
    {
        if ($menu->conference_id !== $conference->id || $data->menu_id !== $menu->id) {
            return $this->errorResponse('Resource not found', 404);
        }

        try {
            $currentOrder = $data->order;
            
            // Delete the component
            $data->delete();
            
            // Reorder remaining components to fill the gap
            $menu->pageData()
                ->where('order', '>', $currentOrder)
                ->decrement('order');

            return $this->successResponse(
                null,
                'Page component deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete component: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the position of the specified resource - Simple up/down movement.
     *
     * @param  \App\Http\Requests\PageData\UpdatePositionRequest  $request
     * @param  \App\Models\Conference  $conference
     * @param  \App\Models\PageMenu  $menu
     * @param  \App\Models\PageData  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePosition(UpdatePositionRequest $request, Conference $conference, PageMenu $menu, PageData $data)
    {
        if ($menu->conference_id !== $conference->id || $data->menu_id !== $menu->id) {
            return $this->errorResponse('Resource not found', 404);
        }

        $direction = $request->input('direction'); // 'up' or 'down'
        $currentOrder = $data->order;

        try {
            if ($direction === 'up') {
                // Find the component directly above (lower order number)
                $targetData = $menu->pageData()
                    ->where('order', '<', $currentOrder)
                    ->orderBy('order', 'desc')
                    ->first();

                if (!$targetData) {
                    return $this->errorResponse('Cannot move component up - already at the top', 400);
                }

                // Swap positions
                $targetOrder = $targetData->order;
                $targetData->update(['order' => $currentOrder, 'updated_by' => auth()->id()]);
                $data->update(['order' => $targetOrder, 'updated_by' => auth()->id()]);

            } elseif ($direction === 'down') {
                // Find the component directly below (higher order number)
                $targetData = $menu->pageData()
                    ->where('order', '>', $currentOrder)
                    ->orderBy('order', 'asc')
                    ->first();

                if (!$targetData) {
                    return $this->errorResponse('Cannot move component down - already at the bottom', 400);
                }

                // Swap positions
                $targetOrder = $targetData->order;
                $targetData->update(['order' => $currentOrder, 'updated_by' => auth()->id()]);
                $data->update(['order' => $targetOrder, 'updated_by' => auth()->id()]);

            } else {
                return $this->errorResponse('Invalid direction. Use "up" or "down"', 400);
            }

            // Fetch all components in their new order for the response
            $orderedItems = $menu->pageData()
                ->orderBy('order', 'asc')
                ->get();

            return $this->successResponse(
                PageDataResource::collection($orderedItems),
                'Component position updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update position: ' . $e->getMessage(), 500);
        }
    }
}