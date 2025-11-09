<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BasketController;
use App\Http\Controllers\Api\v1\CatalogController;
use App\Http\Controllers\Api\v1\MediaController;
use App\Http\Controllers\Api\v1\NewsController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\ProfileController;
use App\Http\Controllers\Api\v1\SearchController;
use App\Http\Controllers\Api\v1\TagController;
use App\Http\Controllers\Api\v1\TestController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware('throttle:global')->group(function () {
    Route::group(['prefix' => 'v1'], function () {

        Route::get('/testControllerAll', [TestController::class, 'indexList']);
        Route::group(['prefix' => 'auth'], function () {
            Route::post('/register', [AuthController::class, 'register'])
                ->middleware('throttle:auth');
            Route::post('/refreshToken', [AuthController::class, 'refreshToken'])
                ->middleware(['auth:sanctum']);

            Route::post('/login', [AuthController::class, 'login'])
                ->middleware('throttle:auth');
            Route::post('/logout', [AuthController::class, 'logout'])
                ->middleware('auth:sanctum');
        });

        Route::group([
            'prefix' => 'user',
            'middleware' => 'auth:sanctum'
        ], function () {
            Route::get('/data', [UserController::class, 'me'])
                ->middleware('throttle:api');
            Route::patch('/data', [UserController::class, 'meUpdate'])
                ->middleware('throttle:api');
            Route::get('/{user:email}/user-data', [UserController::class, 'userData'])
                ->middleware('throttle:api');
        });

        Route::group([
            'prefix' => 'profile',
            'middleware' => 'auth:sanctum'
        ], function () {
            Route::get('/', [ProfileController::class, 'meProfiles'])
                ->middleware('throttle:api');
            Route::post('/', [ProfileController::class, 'create'])
                ->middleware('throttle:content');
            Route::get('/{profile:inn}', [ProfileController::class, 'profileByInn'])
                ->middleware('throttle:api');
            Route::patch('/{profile:inn}', [ProfileController::class, 'profileUpdateByInn'])
                ->middleware('throttle:content');
            Route::delete('/{profile:inn}', [ProfileController::class, 'deleteProfile'])
                ->middleware('throttle:content');
        });

        Route::group([
            'prefix' => 'news',
        ], function () {
            Route::get('/', [NewsController::class, 'index']);
            Route::post('/', [NewsController::class, 'create'])
                ->middleware(['auth:sanctum', 'throttle:content']);
            Route::get('/{slug}', [NewsController::class, 'show']);
            Route::post('/{news:slug}', [NewsController::class, 'update'])
                ->middleware(['auth:sanctum', 'throttle:content']);
            Route::delete('/{news:slug}', [NewsController::class, 'delete'])
                ->middleware(['auth:sanctum', 'throttle:content']);
        });

        Route::group([
            'prefix' => 'search',
        ], function () {
            Route::get('/', [SearchController::class, 'search'])
                ->middleware('throttle:search');
        });

        Route::group([
            'prefix' => 'tags',
        ], function () {
            Route::get('/', [TagController::class, 'index']);
            Route::post('/', [TagController::class, 'create'])
                ->middleware(['auth:sanctum', 'throttle:content']);
        });

        Route::group([
            'prefix' => 'catalog',
        ], function () {
            Route::get('/', [CatalogController::class, 'index']);
            Route::post('/', [CatalogController::class, 'create'])
                ->middleware(['auth:sanctum', 'throttle:content']);
            Route::get('/{slug}', [CatalogController::class, 'show'])
                ->middleware('optional-sanctum');
            Route::post('/{product:slug}', [CatalogController::class, 'update'])
                ->middleware(['auth:sanctum', 'throttle:content']);
            Route::delete('/{product:slug}', [CatalogController::class, 'delete'])
                ->middleware(['auth:sanctum', 'throttle:content']);
        });

        Route::group([
            'prefix' => 'media',
        ], function () {
            Route::delete('/', [MediaController::class, 'deletedImage'])
                ->middleware(['auth:sanctum', 'throttle:content']);
        });

        Route::group([
            'prefix' => 'cart',
        ], function () {
            Route::get('/', [BasketController::class, 'index'])
                ->middleware('optional-sanctum');
            Route::post('/', [BasketController::class, 'store'])
                ->middleware('optional-sanctum');
            Route::delete('/', [BasketController::class, 'delete'])
                ->middleware('optional-sanctum');
        });

        Route::group([
            'prefix' => 'order',
        ], function () {
            Route::get('/', [OrderController::class, 'index'])
                ->middleware(['auth:sanctum', 'throttle:api']);
            Route::post('/', [OrderController::class, 'store'])
                ->middleware(['auth:sanctum', 'throttle:api']);
            Route::get('/cart', [OrderController::class, 'getCart'])
                ->middleware('optional-sanctum');
            Route::patch('/{order:id}/cancel', [OrderController::class, 'cancelOrder'])
                ->middleware(['auth:sanctum', 'throttle:api']);
            Route::post('/repeat', [OrderController::class, 'repeatOrder'])
                ->middleware(['auth:sanctum', 'throttle:api']);
            Route::get('/statuses', [OrderController::class, 'statuses']);
            Route::get('/pay-types', [OrderController::class, 'payTypes']);
            Route::patch('/{order:id}/change-status', [OrderController::class, 'changeOrderStatus'])
                ->middleware(['auth:sanctum', 'throttle:api']);
        });
    });

});


