<?php

namespace Database\Seeders;

use App\Models\Orders\OrderStatus;
use App\Models\Orders\PayType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderPropertiesSeeder extends Seeder
{
    public function run(): void
    {
        if (OrderStatus::get()->count() == 0) {
            $this->orderStatusesCreate();
        }

        if (PayType::get()->count() == 0) {
            $this->payTypesCreate();
        }
    }

    private function payTypesCreate(): void
    {
        PayType::factory()->create([
            'title' => 'Онлайн',
            'slug' => 'online'
        ]);
        PayType::factory()->create([
            'title' => 'Офлайн',
            'slug' => 'offline'
        ]);
    }

    private function orderStatusesCreate(): void
    {
        OrderStatus::factory()->create([
            'title' => 'Принят',
            'slug' => 'prinyat'
        ]);
        OrderStatus::factory()->create([
            'title' => 'На рассмотрении менеджера',
            'slug' => 'manager'
        ]);
        OrderStatus::factory()->create([
            'title' => 'Отменен',
            'slug' => 'cancelled'
        ]);
        OrderStatus::factory()->create([
            'title' => 'Выполнен',
            'slug' => 'completed'
        ]);
        OrderStatus::factory()->create([
            'title' => 'Доставка',
            'slug' => 'delivery'
        ]);
    }
}
