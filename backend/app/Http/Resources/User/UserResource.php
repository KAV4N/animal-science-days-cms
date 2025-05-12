<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'roles' => $this->whenLoaded('roles', function() {
                return $this->roles->pluck('name');
            }),
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'must_change_password' => $this->must_change_password,
            'updated_at' => $this->when($this->updated_at, function() {
                return $this->updated_at->toIso8601String();
            }),
            'created_at' => $this->when($this->created_at, function() {
                return $this->created_at->toIso8601String();
            }),
        ];
    }
}