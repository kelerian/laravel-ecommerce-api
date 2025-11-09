<?php

namespace Database\Factories\Products;

use App\Models\Products\PriceName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products\PriceName>
 */
class PriceNameFactory extends Factory
{

    public function definition(): array
    {
        $names = [
            'Розничная цена' => 'rozn',
            'Оптовая цена' => 'opt',
            'Специальная цена' => 'spec',
        ];

        // Берем случайное название которого еще нет в базе
        $existing = PriceName::pluck('name')->toArray();
        $available = array_diff_key($names, array_flip($existing));

        if (empty($available)) {
            // Если все есть - берем любое
            $name = array_key_first($names);
        } else {
            // Берем случайное из недостающих
            $name = array_rand($available);
        }

        return [
            'name' => $name,
            'slug' => $names[$name],
        ];
    }
}
