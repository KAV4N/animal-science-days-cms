<?php

namespace App\Http\Resources\Conference;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'university' => $this->whenLoaded('university'),
            'pivot' => [
                'conference_id' => $this->pivot->conference_id,
                'assigned_by' => $this->pivot->assigned_by,
                'assigned_by_user' => new UserResource(User::find($this->pivot->assigned_by)),
                'created_at' => $this->pivot->created_at,
                'updated_at' => $this->pivot->updated_at,
            ],
        ];
    }
}