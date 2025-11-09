<?php

namespace App\Http\Resources\Media;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NewsResource extends JsonResource
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
            'slug' => $this->slug,
            'preview_picture' => $this->preview_picture ? Storage::url($this->preview_picture) : null,
            'created_at' => $this->created_at,
            'tags' => TagsResource::collection($this->whenLoaded('tags')),
            'author' => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ]: null,

        ];
    }
}
