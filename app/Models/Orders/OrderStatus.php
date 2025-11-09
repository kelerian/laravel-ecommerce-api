<?php

namespace App\Models\Orders;

use Database\Factories\Orders\OrderStatusesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'order_statuses';

    public const PRINYAT = 'prinyat';
    public const MANAGER = 'manager';
    public const CANCELLED = 'cancelled';

    public static function canBeCancelled(string $status): bool
    {
        return in_array($status, [self::PRINYAT, self::MANAGER]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    protected static function newFactory()
    {
        return OrderStatusesFactory::new();
    }

}
