<?php

namespace App\Services\Carts;

use App\Helpers\Helper;
use App\Models\Carts\Cart;

class CalculatingPriceCartService
{

    private ?Cart $cart = null;
    private $priceThresholds;

    private const PRICE_TYPES = [
        'basic' => 'rozn',
        'average' => 'spec',
        'wholesale' => 'opt'
    ];
    private ?string $selectPrice = null;

    public function __construct(){

        $this->priceThresholds = config('products.basket_price_thresholds', [
            'opt' => 100000,
            'spec' => 50000
        ]);
    }

    public function getCalculatedCart(): ?Cart
    {
        return $this->cart;
    }

    public function calculateCart(Cart $cart): CalculatingPriceCartService
    {
        $this->cart = $cart;
        $this->calculatingCountProductInCart();
        $this->calculatingPricesInCart();
        $this->selectPriceType();
        $this->selectPriceTypeToProduct();

        return $this;
    }

    private function calculatingCountProductInCart(): void
    {
        if (!isset($this->cart)) {
            return ;
        }
        $this->cart->setAttribute('product_count', $this->cart->products->count());
    }


    private function calculatingPricesInCart(): void
    {
        if (!isset($this->cart)) {
            return ;
        }

        $calculated = [];

        $this->cart->products->each(function ($product) use (&$calculated) {
            $quantity = $product->pivot->quantity ?? 0;

            $product->priceName->each(function ($price) use (&$calculated, $quantity) {
                $allPrice = Helper::formatPrice($price->pivot->price * $quantity);
                $price->pivot->total_price_product = $allPrice;
                if (!isset($calculated[$price->slug])) {
                    $calculated[$price->slug] = [
                        'id' => $price->id,
                        'slug' => $price->slug,
                        'name' => $price->name,
                        'total_price' => $allPrice,
                        'selected' => false,
                    ];
                } else {
                    $calculated[$price->slug]['total_price'] += $allPrice;
                }
                foreach ($calculated as &$price){
                    $price['total_price'] = Helper::formatPrice($price['total_price']);
                }
            });
        });

        $this->cart->setAttribute('calculate_prices', $calculated);
    }

    private function selectPriceType(): void
    {
        if (!isset($this->cart?->calculate_prices[self::PRICE_TYPES['basic']])) {

            return ;
        }
        $calculatePrices = $this->cart->calculate_prices;

        $rozn = $this->cart->calculate_prices[self::PRICE_TYPES['basic']]['total_price'] ?? 0;
        $opt = $this->cart->calculate_prices[self::PRICE_TYPES['wholesale']]['total_price'] ?? null;
        $spec = $this->cart->calculate_prices[self::PRICE_TYPES['average']]['total_price'] ?? null;

        if ($rozn > $this->priceThresholds['opt'] && $opt) {
            $calculatePrices[self::PRICE_TYPES['wholesale']]['selected'] = true;
            $this->selectPrice = self::PRICE_TYPES['wholesale'];
        } elseif ($rozn > $this->priceThresholds['spec'] && $spec) {
            $calculatePrices[self::PRICE_TYPES['average']]['selected'] = true;
            $this->selectPrice = self::PRICE_TYPES['average'];
        } else {
            $calculatePrices[self::PRICE_TYPES['basic']]['selected'] = true;
            $this->selectPrice = self::PRICE_TYPES['basic'];
        }
        $this->cart->calculate_prices = $calculatePrices;

    }

    private function selectPriceTypeToProduct(): void
    {
        if (!isset($this->cart)) {
            return;
        }
        $this->cart->products->each(function ($product) {
            $typePrice = $this->selectPrice;

            $product->priceName->each(function ($price) use ($typePrice){
                $price->selected = $price->slug == $typePrice;
            });
        });
    }
}
