<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageData\StorePageDataRequest;
use App\Http\Requests\PageData\UpdatePageDataRequest;
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

        $pageData = PageData::create($validated);

        return $this->successResponse(
            new PageDataResource($pageData),
            'Page data created successfully',
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

        $data->update($validated);

        return $this->successResponse(
            new PageDataResource($data),
            'Page data updated successfully'
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

        $data->delete();

        return $this->successResponse(
            null,
            'Page data deleted successfully'
        );
    }
}