<?php
namespace App\Http\Resources\University;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'country' => $this->country,
            'city' => $this->city,
        ];
    }
}