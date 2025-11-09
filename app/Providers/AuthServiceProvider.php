<?php

namespace App\Providers;

use App\Models\Media\News;
use App\Models\Media\Tag;
use App\Models\Products\Product;
use App\Models\Users\Profile;
use App\Models\Users\User;
use App\Policies\NewsPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        User::class => UserPolicy::class,
        Profile::class => ProfilePolicy::class,
        News::class => NewsPolicy::class,
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
        Gate::define('delete-images', function (User $user, News|Product|User $obj) {
            if ($obj instanceof News) {
                return $user->hasGroup('admin') || ($obj->author?->id === $user->id);
            }

            if ($obj instanceof Product) {
                return $user->hasGroup('admin');
            }

            if ($obj instanceof User) {
                return $user->hasGroup('admin') || $obj->id === $user->id;
            }
            return false;
        });

        Gate::define('create-tag', function (User $user) {

            return $user->hasGroup('admin');
        });
   }
}
