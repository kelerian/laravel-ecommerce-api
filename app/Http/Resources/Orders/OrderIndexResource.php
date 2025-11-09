<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderIndexResource extends JsonResource
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
            'order_status' => $this->orderStatus->title,
            'pay_type' => $this->payType->title,
            'final_price' => $this->final_price,
            'phone' => $this->phone,
            'address' => $this->address,
            'orderItem' => OrderDetailResource::collection($this->orderItem),
        ];
        return parent::toArray($request);
    }
}
