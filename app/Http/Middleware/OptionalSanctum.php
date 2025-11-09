<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctum
{
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->bearerToken()) {
            try {
                auth()->setUser(
                    \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable
                );
            } catch (\Exception $e){
                \Log::warning('Optional auth failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $next($request);
    }
}
