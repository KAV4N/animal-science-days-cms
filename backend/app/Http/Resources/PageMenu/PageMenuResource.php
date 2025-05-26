<?php

namespace App\Http\Resources\PageMenu;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PageData\PageDataResource;

class PageMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'conference_id' => $this->conference_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'order' => $this->order,
            'is_published' => (bool) $this->is_published,
            'page_data' => $this->whenLoaded('pageData', function () {
                return PageDataResource::collection($this->pageData);
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}