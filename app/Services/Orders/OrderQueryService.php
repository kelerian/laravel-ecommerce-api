<?php

namespace App\Services\Orders;

use App\Dto\Order\FilterForOrderListDto;
use App\Models\Users\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderQueryService
{
    public function __construct(
        private OrderListQuery $orderListQuery,
    )
    {}
    public function orderListWithFilter(FilterForOrderListDto $dto, User $user): LengthAwarePaginator
    {
        return $this->orderListQuery
            ->applyAccessControl($user, $dto->allOrders)
            ->filterByUserId($dto->userId, $user)
            ->filterByEmail($dto->email)
            ->filterByDateRange($dto->dateFrom, $dto->dateTo)
            ->filterByPayType($dto->payType)
            ->filterByOrderStatus($dto->orderStatus)
            ->paginateWithSort($dto->limit, $dto->sort, $dto->direction);
    }
}
