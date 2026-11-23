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
                $destination = url()->previous($fallback);

                return redirect()->to($this->withExpiredFlag($destination));
            }

            return redirect()->route('login', ['expired' => 1]);
        }
    }

    private function withExpiredFlag(string $url): string
    {
        $fragment = '';

        if (($fragmentPosition = strpos($url, '#')) !== false) {
            $fragment = substr($url, $fragmentPosition);
            $url = substr($url, 0, $fragmentPosition);
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'expired=1'.$fragment;
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
