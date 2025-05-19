<?php

namespace App\Http\Resources\Conference;

use Illuminate\Http\Resources\Json\JsonResource;

class DecadeResource extends JsonResource
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
            'decade' => $this->resource['decade'],
            'count' => $this->resource['count'],
        ];
    }
}