<?php

namespace App\Console\Commands;

use App\Models\Carts\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearingUnusedCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:clearing-unused-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка неиспользуемых корзин';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = config('params.basket_deletion_deadline');
        $targetDate = now()->subDays($config);
        $oldCartIds = Cart::where('updated_at','<',$targetDate)->pluck('id');
        if ($oldCartIds->isNotEmpty()) {
            DB::table('cart_items')
                ->whereIn('cart_id',$oldCartIds)
                ->delete();

            Cart::whereIn('id', $oldCartIds)->update(['updated_at' => now()]);
        }

    }
}
