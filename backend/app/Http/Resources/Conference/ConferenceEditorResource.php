<?php
namespace App\Http\Resources\Conference;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ConferenceEditorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'conference_id' => $this->conference_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'assigned_by' => new UserResource($this->whenLoaded('assignedByUser')),
            'assigned_at' => $this->assigned_at,
        ];
    }
}