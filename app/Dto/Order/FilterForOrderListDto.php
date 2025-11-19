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
            limit: $data['limit'] ?? 10,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
            email: $data['email'] ?? false,
            userId: $data['user_id'] ?? false,
            allOrders: filter_var($data['all_orders'] ?? false, FILTER_VALIDATE_BOOLEAN),
            payType: $data['pay_type'] ?? false,
            orderStatus: $data['order_status'] ?? false,
            dateFrom: $data['date_from'] ?? false,
            dateTo: $data['date_to'] ?? false
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
