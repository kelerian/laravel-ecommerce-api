<?php

namespace App\Services\Orders;

use App\Dto\Order\FilterForOrderListDto;
use App\Models\Orders\Order;
use App\Models\Users\User;

class OrderQueryServices
{
    public function orderListWithFilter(FilterForOrderListDto $dto, User $user)
    {
        return Order::query()
            ->select(
            'orders.id',
            'orders.created_at',
            'orders.user_id',
            'orders.order_status_id',
            'orders.final_price',
            'orders.email',
            'orders.phone',
            'orders.address',
            'orders.pay_type_id',
        )
            ->with([
                'orderStatus' => function ($query) {
                    $query->select('order_statuses.id', 'order_statuses.slug', 'order_statuses.title');
                },
                'payType' => function ($query) {
                    $query->select('pay_types.id', 'pay_types.slug', 'pay_types.title');
                },
                'orderItem.product'
            ])
            ->when(!$user->isAdmin() || !$dto->allOrders, function ($query) use ($user) {
                $query->where('orders.user_id', $user->id);
            })
            ->when($dto->userId, function ($query) use ($dto) {
                $query->where('orders.user_id', $dto->userId);
            })
            ->when($dto->dateFrom, function ($query) use ($dto) {
                $query->where('orders.created_at', '>=', $dto->dateFrom);
            })
            ->when($dto->dateTo, function ($query) use ($dto) {
                $query->where('orders.created_at', '<=', $dto->dateTo);
            })
            ->when($dto->email, function ($query) use ($dto) {
                $query->where('orders.email', '=', $dto->email);
            })
            ->when($dto->payType, function ($query) use ($dto) {
                $query->whereHas('payType', function ($q) use ($dto) {
                    $q->where('slug', $dto->payType);
                });
            })
            ->when($dto->orderStatus, function ($query) use ($dto) {
                $query->whereHas('orderStatus', function ($q) use ($dto) {
                    $q->where('slug', $dto->orderStatus);
                });
            })
            ->orderBy($dto->sort, $dto->direction)
            ->distinct()
            ->paginate($dto->limit);
    }

}
