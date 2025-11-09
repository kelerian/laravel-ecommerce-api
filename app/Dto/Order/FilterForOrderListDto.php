<?php

namespace App\Dto\Order;

class FilterForOrderListDto
{
    public function __construct(
        public int $limit,
        public string $sort,
        public string $direction,
        public string|bool $email,
        public int|bool $userId,
        public bool $allOrders,
        public string|bool $payType,
        public string|bool $orderStatus,
        public string|bool $dateFrom,
        public string|bool $dateTo
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            limit: $data['limit'],
            sort: $data['sort'],
            direction: $data['direction'],
            email: $data['email'],
            userId: $data['user_id'],
            allOrders: $data['all_orders'],
            payType: $data['pay_type'],
            orderStatus: $data['order_status'],
            dateFrom: $data['date_from'],
            dateTo: $data['date_to']
        );
    }

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();

        $result = [];
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }

        return $result;
    }
}
