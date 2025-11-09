<?php

namespace Database\Factories\Products;

use App\Models\Products\PriceName;
use App\Models\Products\Product;
use App\Models\Products\StockName;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(2),
            'description' => fake()->sentence(10),
        ];
    }

    public function withPrice(): static
    {
        return $this->afterCreating(function (Product $product) {

            $priceNames = collect();

            $priceNames->push(PriceName::firstOrCreate(
                ['slug' => 'rozn'],
                ['name' => 'Розничная цена']
            ));

            $priceNames->push(PriceName::firstOrCreate(
                ['slug' => 'opt'],
                ['name' => 'Оптовая цена']
            ));

            $priceNames->push(PriceName::firstOrCreate(
                ['slug' => 'spec'],
                ['name' => 'Специальная цена']
            ));

            foreach ($priceNames as $priceName) {
                $product->price()->create([
                    'price_name_id' => $priceName->id,
                    'price' => $this->generatePriceForType($priceName->name)
                ]);
            }
        });
    }

    protected function generatePriceForType(string $priceName): float
    {
        return match($priceName){
            'Розничная цена' => fake()->randomFloat(2,1000,5000),
            'Оптовая цена' => fake()->randomFloat(2,500,1000),
            'Специальная цена' => fake()->randomFloat(2,300,500),
            default => fake()->randomFloat(2, 100, 200)
        };
    }

    public function withQuantity(): static
    {
        return $this->afterCreating(function (Product $product) {
            $stockNames = collect();

            $stockNames->push(StockName::firstOrCreate(
                ['slug' => 'moskov'],
                ['title' => 'stock_moskov', 'is_active' => true]
            ));

            $stockNames->push(StockName::firstOrCreate(
                ['slug' => 'krasnodar'],
                ['title' => 'stock_krasnodar', 'is_active' => true]
            ));

            $stockNames->push(StockName::firstOrCreate(
                ['slug' => 'ivanovo'],
                ['title' => 'stock_ivanovo', 'is_active' => true]
            ));

            foreach ($stockNames as $stockName) {
                $product->quantity()->create([
                    'stock_name_id' => $stockName->id,
                    'quantity' => fake()->randomNumber(1, 1000)
                ]);
            }
        });
    }
}
