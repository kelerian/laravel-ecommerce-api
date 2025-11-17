<?php

namespace App\Services\Orders;

use App\Models\Orders\Order;
use App\Models\Users\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderListQuery
{
    private $query;

    public function __construct()
    {
        $this->query = Order::query()
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
            ]);
    }

    public function applyAccessControl(User $user, bool $allOrders): self
    {
        if (!$user->isAdmin() || !$allOrders) {
            $this->query->where('orders.user_id', $user->id);
        }
        return $this;
    }


    public function filterByEmail(string|bool $email): self
    {
        if ($email) {
            $this->query->where('orders.email', $email);
        }
        return $this;
    }

    public function filterByUserId(int|bool $userId, User $user): self
    {
        if ($userId && $user->isAdmin()) {
            $this->query->where('orders.user_id', $userId);
        }
        return $this;
    }

    public function filterByDateRange(string|bool $dateFrom, string|bool $dateTo): self
    {
        if ($dateFrom) {
            $this->query->where('orders.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $this->query->where('orders.created_at', '<=', $dateTo);
        }
        return $this;
    }

    public function filterByPayType(string|bool $payType): self
    {
        if ($payType) {
            $this->query->whereHas('payType', function ($q) use ($payType) {
                $q->where('slug', $payType);
            });
        }
        return $this;
    }

    public function filterByOrderStatus(string|bool $orderStatus): self
    {
        if ($orderStatus) {
            $this->query->whereHas('orderStatus', function ($q) use ($orderStatus) {
                $q->where('slug', $orderStatus);
            });
        }
        return $this;
    }

    public function paginateWithSort(int $limit, string $sort, string $direction): LengthAwarePaginator
    {
        return $this->query
            ->orderBy($sort, $direction)
            ->distinct()
            ->paginate($limit);
    }
}
