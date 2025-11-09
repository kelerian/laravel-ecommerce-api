<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ImagesResource extends JsonResource
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
            'mime_type' => $this->mime_type,
            'file_name' => Storage::url($this->file_name),
            'generated_conversions' => collect($this->generated_conversions)->mapWithKeys(function($path, $key) {
                return [$key => $path ? Storage::url($path) : null];
            }),
        ];
    }
}
