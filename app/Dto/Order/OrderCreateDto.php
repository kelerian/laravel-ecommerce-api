<?php

namespace App\Dto\Order;

class OrderCreateDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $email,
        public readonly string $phone,
        public readonly string $address,
        public readonly string $pay_type,
        public readonly string $fuser_id,
        public readonly int $user_id,

    )
    {}

    public static function fromArray(array $arr): OrderCreateDto
    {
        return new self(
            email: $arr['email'],
            phone: $arr['phone'],
            address: $arr['address'],
            pay_type: $arr['pay_type'],
            fuser_id: $arr['fuser_id'],
            user_id: $arr['user_id']
        );
    }
}
