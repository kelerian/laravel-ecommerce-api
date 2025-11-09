<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->plusUser->lastname,
            'email' => $this->email,
            'gender' => $this->gender->gender_type ?? null,
            'birthday' => $this->plusUser->birthday,
            'address' => $this->plusUser->address,
            'fuser_id' => $this->cart?->fuser_id,
            'role' => $this->groups->map(function ($group){
                return [
                    'title' => $group->title,
                    'slug' => $group->slug,
                ];
            }),
        ];
    }
}
