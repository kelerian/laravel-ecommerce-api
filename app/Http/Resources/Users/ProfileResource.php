<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'title' => $this->title,
            'inn' => $this->inn,
            'address' => $this->address,
            'phone' => $this->phone,
            'date_create' => $this->created_at->toDateTimeString(),
        ];
    }
}
