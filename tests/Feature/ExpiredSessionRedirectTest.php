<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class ExpiredSessionRedirectTest extends TestCase
{
    public function test_expired_web_session_redirects_to_login_with_a_message(): void
    {
        $request = Request::create('/postlogin', 'POST');
        $request->setLaravelSession($this->app['session']->driver());

        $response = $this->app->make(\App\Exceptions\Handler::class)
            ->render($request, new TokenMismatchException);

        $this->assertTrue($response->isRedirect(route('login')));
        $this->assertSame(
            'Sesi Anda telah berakhir. Silakan masuk kembali.',
            $request->session()->get('error')
        );
    }

    public function test_authenticated_user_returns_to_the_previous_page_with_a_message(): void
    {
        $this->be(new User([
            'name' => 'Pengguna',
            'email' => 'pengguna@example.test',
        ]));

        $request = Request::create('/suratketeranganaktif', 'POST', server: [
            'HTTP_REFERER' => route('home'),
        ]);
        $request->setLaravelSession($this->app['session']->driver());

        $response = $this->app->make(\App\Exceptions\Handler::class)
            ->render($request, new TokenMismatchException);

        $this->assertTrue($response->isRedirect(route('home')));
        $this->assertSame(
            'Halaman telah kedaluwarsa. Silakan coba kembali.',
            $request->session()->get('error')
        );
    }

    public function test_expired_json_session_keeps_the_419_status(): void
    {
        $request = Request::create('/postlogin', 'POST', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = $this->app->make(\App\Exceptions\Handler::class)
            ->render($request, new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertSame(
            'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
            json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR)['message']
        );
    }
}
