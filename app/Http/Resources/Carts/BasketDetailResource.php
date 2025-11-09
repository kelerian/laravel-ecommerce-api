<?php

namespace App\Http\Resources\Carts;

use App\Http\Resources\Media\ImagesResource;
use App\Http\Resources\Product\PriceResource;
use App\Http\Resources\Product\StockResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BasketDetailResource extends JsonResource
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
            'preview_picture' => isset($this->preview_picture) ? Storage::url($this->preview_picture) : null,
            'quantity' => $this->pivot->quantity,
            'all_quantity_in_stock' => $this->all_quantity_in_stock,
            'price' => PriceInCartResource::collection($this->whenLoaded('priceName')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];

    }
}
