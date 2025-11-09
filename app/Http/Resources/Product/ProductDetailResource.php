<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Media\ImagesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductDetailResource extends JsonResource
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
            'created_at' => $this->created_at,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'preview_picture' => $this->preview_picture ? Storage::url($this->preview_picture) : null,
            'images' => ImagesResource::collection($this->whenLoaded('images')),
            'price' => PriceResource::collection($this->whenLoaded('priceName')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
