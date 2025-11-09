<?php

namespace App\Services\Carts;

use App\Models\Carts\Cart;
use App\Models\Products\Product;

class CalculatingQuantityCartService
{

    public function calculateQuantityInStock(Cart $cart): void
    {
        if (!isset($cart)) {
            return ;
        }

        $cart->products->each(function ($product) use (&$calculated) {
            $product->all_quantity_in_stock = $product->stocks
                ->sum(fn($stock) => $stock->pivot->quantity ?? 0);
        });
    }

    public function checkQuantityInStock(array $productsData): array
    {
        $processedArray = [];
        $productIdsForQuery = array_column($productsData,'id');
        $productsQuery = Product::whereIn('id', $productIdsForQuery)->with('stocks')->get();
        $productsQuery->each(function ($product) use (&$processedArray){
            $sumQuantityInAllStock = $product->stocks->sum(fn($stock) => $stock->pivot->quantity ?? 0);

            $processedArray[$product->id] = $sumQuantityInAllStock;
        });
        foreach ($productsData as $key => &$product) {
            if (isset($processedArray[$product['id']])
                && $processedArray[$product['id']] < $product['quantity']
            ) {
                $product['quantity'] = $processedArray[$product['id']];
            }

            if (!isset($processedArray[$product['id']]) || $product['quantity'] <= 0)  {
                unset($productsData[$key]);
            }
        }
        return $productsData;
    }
}
