<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
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

        return parent::render($request, $e);
    }
}
