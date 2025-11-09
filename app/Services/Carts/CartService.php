<?php

namespace App\Services\Carts;

use App\Models\Carts\Cart;


class CartService
{
    private ?Cart $cart = null;
    private ?string $fuserId = null;


    public function __construct(
        private CalculatingPriceCartService $calcCartPriceServ,
        private CalculatingQuantityCartService $calcCartQuantServ,
    ){}

    public function loadCart($fuserId): CartService
    {
        $this->fuserId = $fuserId;
        $this->cart = $this->fetchCart();

        if (!$this->cart) {
            $this->createCartForFuser();
            $this->cart = $this->fetchCart();
        }

        $this->calcCartQuantServ->calculateQuantityInStock($this->cart);

        $this->cart = $this->calcCartPriceServ
            ->calculateCart($this->cart)
            ->getCalculatedCart();

        return $this;
    }

    private function fetchCart(): ?Cart
    {
        return Cart::with(['products', 'products.priceName', 'products.stocks', 'products.images'])
            ->where('fuser_id', $this->fuserId)
            ->first();
    }

    private function createCartForFuser(): void
    {
        Cart::create([
            'fuser_id' => $this->fuserId,
        ]);
    }


    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function updateCart(array $productsData): array
    {
        $productsData = $this->calcCartQuantServ->checkQuantityInStock($productsData);
        $productToUpdate = [];
        foreach ($productsData as $product){
            $productToUpdate[(int)$product['id']] = ['quantity' => (int)$product['quantity']];
        }
        $result = $this->cart->products()->syncWithoutDetaching($productToUpdate);
        Cart::where('id', $this->cart->id)->update(['updated_at' => now()]);

        return $result;
    }


    public function deleteItemFromCart(array $productsId): void
    {
        $this->cart->products()->detach($productsId);
        Cart::where('id', $this->cart->id)->update(['updated_at' => now()]);
    }

    public function clearCart(): void
    {
        $this->cart->products()->detach();
        Cart::where('id', $this->cart->id)->update(['updated_at' => now()]);
    }


}
