<?php

namespace App\Services\Orders;

use App\Models\Orders\Order;

class OrderToCartConverterServices
{


    public function converter(Order $order): array
    {
        $transformedProducts = [];
        $products = $order->changes_in_stock;
        foreach ($products as $productId => $productData) {
            $transformedProducts[$productId] = [
                'id' => $productId,
                'quantity' => 0
            ];

            foreach ($productData as $stockData) {
                $transformedProducts[$productId]['quantity'] += $stockData['taken_quantity'];
            }
        }
        return array_values($transformedProducts);
    }
}
