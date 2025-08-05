<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SlideshowSecurity
{
    /**
     * Handle an incoming request untuk slideshow
     * Middleware ini menambahkan keamanan ekstra untuk slideshow
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limiting sederhana untuk API slideshow
        if ($request->is('slideshow/media')) {
            $key = 'slideshow_api_' . $request->ip();
            $maxAttempts = 120; // 120 request per menit
            $decayMinutes = 1;

            if (cache()->has($key)) {
                $attempts = cache()->get($key);
                if ($attempts >= $maxAttempts) {
                    return response()->json([
                        'error' => 'Rate limit exceeded'
                    ], 429);
                }
                cache()->put($key, $attempts + 1, $decayMinutes * 60);
            } else {
                cache()->put($key, 1, $decayMinutes * 60);
            }
        }

        // Tambahkan header keamanan
        $response = $next($request);
        
        if ($request->is('slideshow*')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
