<?php

namespace App\Http\Resources\Conference;

use Illuminate\Http\Resources\Json\JsonResource;

class ConferenceResource extends JsonResource
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
            'university_id' => $this->university_id,
            'university' => $this->when($this->relationLoaded('university'), function () {
                return [
                    'id' => $this->university->id,
                    'name' => $this->university->name,
                ];
            }),
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'location' => $this->location,
            'venue_details' => $this->venue_details,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'is_latest' => (bool) $this->is_latest,
            'is_published' => (bool) $this->is_published,
            'editors' => $this->when($this->relationLoaded('editors'), function () {
                return $this->editors->map(function ($editor) {
                    return [
                        'id' => $editor->id,
                        'name' => $editor->name,
                        'email' => $editor->email,
                    ];
                });
            }),
            /*
            'page_menus' => $this->when($this->relationLoaded('pageMenus'), function () {
                return $this->pageMenus->map(function ($menu) {
                    return [
                        'id' => $menu->id,
                        'title' => $menu->title,
                    ];
                });
            }),*/
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
