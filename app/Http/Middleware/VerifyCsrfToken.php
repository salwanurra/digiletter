<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
                ], 419);
            }

            if (auth()->check()) {
                $fallback = auth()->user()->role === 'admin'
                    ? route('home.admin')
                    : route('home');

                return redirect()->back(fallback: $fallback)->with(
                    'error',
                    'Halaman telah kedaluwarsa. Silakan coba kembali.'
                );
            }

            return redirect()->route('login')->with(
                'error',
                'Sesi Anda telah berakhir. Silakan masuk kembali.'
            );
        }
    }

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
