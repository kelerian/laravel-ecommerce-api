<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderDetailResource extends JsonResource
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
            'title' => $this->product_name,
            'preview_picture' => isset($this->product->preview_picture) ? Storage::url($this->product->preview_picture) : null,
            'quantity' => $this->quantity,
            'price' => $this->price,


        ];
        return parent::toArray($request);
    }
}
