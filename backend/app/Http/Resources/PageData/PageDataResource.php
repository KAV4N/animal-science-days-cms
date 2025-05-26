<?php

namespace App\Http\Resources\PageData;

use Illuminate\Http\Resources\Json\JsonResource;

class PageDataResource extends JsonResource
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
            'menu_id' => $this->menu_id,
            'component_type' => $this->component_type,
            'order' => $this->order,
            'data' => $this->data,
            'tag' => $this->tag,
            'is_published' => (bool) $this->is_published,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}