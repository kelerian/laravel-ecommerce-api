<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CatalogIndexResource extends JsonResource
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
            'created_at' => $this->created_at,
            'preview_picture' => isset($this->preview_picture) ? Storage::url($this->preview_picture) : null,
            'price' => PriceResource::collection($this->whenLoaded('priceName')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
