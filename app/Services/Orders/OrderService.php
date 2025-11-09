<?php

namespace App\Services\Orders;

use App\Dto\Order\OrderCreateDto;
use App\Events\ChangeOrderStatusEvent;
use App\Events\NewOrderEvent;
use App\Exceptions\BusinessException;
use App\Models\Carts\Cart;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatus;
use App\Models\Orders\PayType;
use App\Services\Carts\CalculatingPriceCartService;
use App\Services\Carts\CartService;
use Illuminate\Support\Facades\DB;


class OrderService
{

    private $cart = null;


    public function __construct(
        private CartService $cartService,
        private CalculatingPriceCartService  $calcQuantPriceCartServ,
        private InventoryService $inventoryService,
        private OrderToCartConverterServices $convServ,
    )
    {}

    public function createOrder(OrderCreateDto $dto): int
    {
        return DB::transaction(function () use ($dto) {

            $this->loadCartForOrder($dto->fuser_id);
            $this->checkingCartIsEmpty();

            $this->prepareCartForOrder();

            $this->cart = $this->calcQuantPriceCartServ
                ->calculateCart($this->cart)
                ->getCalculatedCart();

            $patTypeId = PayType::where('slug', $dto->pay_type)
                ->firstOrFail()->id;
            $orderStatusId = OrderStatus::where('slug', 'prinyat')
                ->firstOrFail()->id;
            $finalPrice = $this->buildFinalPrice();

            $productToOrder = $this->buildProductForPivotOrder();
            $stockChanges = $this->inventoryService
                ->updateQuantityProductInStock($this->cart, $productToOrder);

            $newOrder = Order::create([
                'email' => $dto->email,
                'phone' => $dto->phone,
                'address' => $dto->address,
                'pay_type_id' => $patTypeId,
                'user_id' => $dto->user_id,
                'final_price' => $finalPrice,
                'order_status_id' => $orderStatusId,
                'changes_in_stock' => $stockChanges,
            ]);

            $newOrder->products()->attach($productToOrder);

            $this->cartService->loadCart($dto->fuser_id)->clearCart();
            $newOrder->loadMissing([
                'user',
                'orderItem',
                'orderStatus',
                'payType'
            ]);
            event(new NewOrderEvent($newOrder));
            return $newOrder->id;
        });
    }

    public function getCartToOrder($fuserId): Cart
    {
        $this->loadCartForOrder($fuserId);
        $this->prepareCartForOrder();
        $this->cart = $this->calcQuantPriceCartServ
            ->calculateCart($this->cart)
            ->getCalculatedCart();
        return $this->cart;
    }


    private function loadCartForOrder($fuserId): void
    {
        $this->cart = $this->cartService
            ->loadCart($fuserId)
            ->getCart();
    }

    private function checkingCartIsEmpty(): void
    {
        if ($this->cart->products->isEmpty()) {
            throw new BusinessException('Cart is empty');
        };
    }

    private function buildFinalPrice(): float
    {
        $finalPrice = 0;
        foreach ($this->cart->calculate_prices as $price){
            if($price['selected']){
                $finalPrice = $price['total_price'];
            }
        }
        return $finalPrice;
    }

    private function buildProductForPivotOrder(): array
    {
        $productToOrder = [];
        $this->cart->products
            ->each(function ($product) use (&$productToOrder){
                $selectedPrice = $product->priceName->firstWhere('selected', true);
                $productToOrder[$product->id] = [
                    'price' => $selectedPrice->pivot->price,
                    'quantity' => $product->pivot->quantity,
                    'product_name'  => $product->title
                ];
            });

        return $productToOrder;
    }

    private function prepareCartForOrder(): void
    {
        $products = $this->cart->products
            ->map(function ($product) {
                if ($product->all_quantity_in_stock < $product->pivot->quantity) {
                    $product->pivot->quantity = $product->all_quantity_in_stock;
                }
                return $product;
            })
            ->filter(function ($product) {
                return $product->all_quantity_in_stock > 0;
            })
            ->values();

        $this->cart->setRelation('products', $products);
    }

    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->orderStatus?->slug != OrderStatus::CANCELLED) {
                $oldStatus = $order->orderStatus;
                $cancelStatus = OrderStatus::where('slug', 'cancelled')->firstOrFail();

                $order->orderStatus()->associate($cancelStatus);
                $order->save();

                event(new ChangeOrderStatusEvent($order, $cancelStatus, $oldStatus));

                $this->inventoryService->returnProductToStock($order->changes_in_stock);
            }
        });
    }

    public function repeatOrder(Order $order, string $fuserId): array
    {
        return DB::transaction(function () use ($order, $fuserId) {
            $cartItems = $this->convServ->converter($order);

            $cart = $this->cartService->loadCart($fuserId);
            $cart->clearCart();
            $result = $cart->updateCart($cartItems);

            return $result;
        });
    }
}
