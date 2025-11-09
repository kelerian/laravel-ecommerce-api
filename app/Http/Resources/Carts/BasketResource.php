<?php

namespace App\Http\Resources\Carts;

use App\Http\Resources\Product\CatalogIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketResource extends JsonResource
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
            'updated_at' => $this->updated_at,
            'product_count' => $this->product_count,
            'calculate_prices' => $this->calculate_prices,
            'products' => BasketDetailResource::collection($this->products),

        ];
        return parent::toArray($request);
    }
}
