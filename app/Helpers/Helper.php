<?php

namespace App\Helpers;

class Helper
{
    static function formatPrice(float $price, int $decimals = 2,): float {
        return round($price, $decimals, PHP_ROUND_HALF_UP);
    }
}
