<?php

namespace App\Services\Orders;

use App\Models\Products\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function updateQuantityProductInStock($cart, array $productData): array
    {
        $stockChanges = [];
        $cart->products->each(function ($product) use ($productData, &$stockChanges) {

            $requestedQty = $productData[$product->id]['quantity'] ?? 0;

            if ($product->all_quantity_in_stock < $requestedQty) {
                throw ValidationException::withMessages([
                    'quantity' => "Not enough items '{$product->title}' in stock",
                ]);
            }
            $remainingQty = $requestedQty;
            $hasChanges = true;

            $product->stocks->each(function ($stock) use (&$remainingQty, &$stockChanges, $product, &$hasChanges) {

                if ($remainingQty <= 0 || $stock->is_active == false) {
                    return false;
                }
                $availableInStock = $stock->pivot->quantity ?? 0;
                $takeQty = min($remainingQty, $availableInStock);

                if ($takeQty > 0) {

                    $stock->pivot->quantity = $availableInStock - $takeQty;
                    $stock->pivot->save();

                    $stockChanges[$product->id][] = [
                        'stock_slug' => $stock->slug,
                        'taken_quantity' => $takeQty,
                    ];

                    $remainingQty -= $takeQty;
                    $hasChanges = true;
                }
            });

             if ($hasChanges) {
                 Cache::tags(['catalog'])->flush();
             }
        });

        return $stockChanges;
    }

    public function returnProductToStock($stockChanges)
    {
        if(!isset($stockChanges)){
            return ;
        }
        $productIds = array_keys($stockChanges);
        $products = Product::whereIn('id', $productIds)
            ->with('stocks')
            ->get();

        $products->each(function ($product) use ($stockChanges) {
            foreach ($stockChanges[$product->id] as $change) {
                $stock = $product->stocks->firstWhere('slug', $change['stock_slug']);
                if ($stock) {
                    $stock->pivot->quantity += $change['taken_quantity'];
                    $stock->pivot->save();
                }
            }
        });
        Cache::tags(['catalog'])->flush();
    }
}
