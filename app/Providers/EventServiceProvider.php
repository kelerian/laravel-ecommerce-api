<?php

namespace App\Providers;

use App\Models\Media\News;
use App\Models\Products\Product;
use App\Observers\NewsObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\SendRegistrationMailListener',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LoginListener',
        ],
        'App\Events\NewOrderEvent' => [
            'App\Listeners\NewOrderListener',
        ],
        'App\Events\ChangeOrderStatusEvent' => [
            'App\Listeners\ChangeOrderStatusListener',
        ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        News::observe(NewsObserver::class);
        Product::observe(ProductObserver::class);
    }
}
