<?php

namespace Database\Factories\Products;

use App\Models\Products\StockName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products\StockName>
 */
class StockNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'stock_moskov' => 'moskov',
            'stock_krasnodar' => 'krasnodar',
            'stock_ivanovo' => 'ivanovo'
        ];

// Проверяем какие slug уже есть в базе
        $existingSlugs = StockName::pluck('slug')->toArray();
        $available = array_diff($titles, $existingSlugs);

        if (empty($available)) {
            $slug = current($titles); // Берем первый slug
            $title = array_search($slug, $titles); // Находим title по slug
        } else {
            $slug = current($available); // Берем первый доступный slug
            $title = array_search($slug, $titles); // Находим title по slug
        }

        return [
            'title' => $title,
            'slug' => $slug,
        ];
    }
}
