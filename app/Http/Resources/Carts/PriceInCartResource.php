<?php

namespace App\Http\Resources\Carts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceInCartResource extends JsonResource
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
            'selected' => $this->selected,
            'price' => $this->whenPivotLoaded('prices', function () {
                return $this->pivot->price;
            }),
            'total_price_product' => $this->whenPivotLoaded('prices', function () {
                return $this->pivot->total_price_product;
            })

        ];
    }
}
