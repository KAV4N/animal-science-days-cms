<?php

namespace App\Http\Resources\Conference;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ConferenceEditorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // For the pivot relationship, we need to transform the User model with its pivot data
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'university' => $this->whenLoaded('university'),
            'pivot' => [
                'conference_id' => $this->pivot->conference_id,
                'assigned_by' => $this->pivot->assigned_by,
            ],
        ];
    }
}