<?php

namespace App\Models\Orders;

use Database\Factories\Orders\PayTypesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayType extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pay_types';

    public function orders():HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function newFactory()
    {
        return PayTypesFactory::new();
    }
}
