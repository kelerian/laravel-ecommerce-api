<?php


use App\Exceptions\BusinessException;
use App\Http\Middleware\OptionalSanctum;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(App\Http\Middleware\ForceJsonToDelete::class);
        $middleware->alias(['optional-sanctum'=>OptionalSanctum::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson() && app()->isProduction()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() && app()->isProduction()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        $exceptions->renderable(function (ModelNotFoundException|NotFoundHttpException $e, $request){
            if ($request->expectsJson() && app()->isProduction()){
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request){
            if($request->expectsJson() && app()->isProduction()){
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                ], 405);
            }
        });

        $exceptions->renderable(function (ThrottleRequestsException $e, $request){
            if($request->expectsJson() && app()->isProduction()){
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests',
                ], 429);
            }
        });

        $exceptions->renderable(function (QueryException $e, $request){
            if($request->expectsJson() && app()->isProduction()){
                \Log::error('Database error',[
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'url' => $request->url(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error'
                ], 500);
            }
        });

        $exceptions->renderable(function (BusinessException $e, $request){
            if ($request->expectsJson()) {
                if (app()->isProduction()) {
                    $response = [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ];

                    if (!empty($e->getData())) {
                        $response['data'] = $e->getData();
                    }
                    return response()->json($response, $e->getCode() ?: 422);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                        'code' => $e->getCode(),
                        'data' => $e->getData(),
                    ], $e->getCode() ?: 422);
                }
            }
        });

        $exceptions->renderable(function (Throwable $e, $request){
            if ($request->expectsJson() && app()->isProduction()){
                \Log::error('Unhandled exception',[
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'url' => $request->url(),
                    'user_id' => $request->user()?->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error'
                ], 500);
            }
        });

    })->withSchedule(function (Schedule $schedule) {
        $schedule->command('tokens:expired-cleanup')
            ->dailyAt('3:00');
        $schedule->command('cart:clearing-unused-cart')
            ->monthlyOn(1, '03:00');
    })->create();
