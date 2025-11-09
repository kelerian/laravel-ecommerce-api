<?php

namespace Database\Factories\Orders;

use App\Models\Orders\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrderStatusesFactory extends Factory
{

    protected $model = OrderStatus::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->title().time();
        return [
            'title' => $title,
            'slug' => Str::slug($title),
        ];
    }
}
