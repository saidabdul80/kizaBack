<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Idempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      
        $idempotencyKey = $request->header('Idempotency-Key');

        if ($idempotencyKey) {
            
            if (Cache::has($idempotencyKey)) {
                return response()->json([
                    'message' => 'Idempotent request: Response returned from cache',
                    'data' => Cache::get($idempotencyKey),
                ]);
            } else {

                $response = $next($request);
                Cache::put($idempotencyKey, $response->getContent(), now()->addSeconds(20));
            }
            
        }

        return $next($request);
    }
}

