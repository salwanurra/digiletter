<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpiredSessionRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', ForceCsrfVerification::class])
            ->post('/_test/expired-session', fn () => response()->noContent());

        Route::middleware(['web', 'auth'])
            ->get('/_test/auth-required', fn () => response()->noContent());

        Route::middleware(['web', 'admin'])
            ->get('/_test/admin-required', fn () => response()->noContent());
    }

    public function test_expired_web_session_redirects_to_login_and_displays_the_message(): void
    {
        $response = $this->post('/_test/expired-session');

        $response->assertRedirect(route('login', ['expired' => 1]));

        $this->get(route('login', ['expired' => 1]))
            ->assertOk()
            ->assertSee('Sesi Anda telah berakhir. Silakan masuk kembali.');
    }

    public function test_authenticated_user_returns_to_the_previous_page_with_an_expired_flag(): void
    {
        $this->be(new User([
            'name' => 'Pengguna',
            'email' => 'pengguna@example.test',
        ]));

        $response = $this->from(route('home'))->post('/_test/expired-session');

        $response->assertRedirect(route('home').'?expired=1');
    }

    public function test_expired_json_session_keeps_the_419_status(): void
    {
        $response = $this->postJson('/_test/expired-session');

        $response
            ->assertStatus(419)
            ->assertJson([
                'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
            ]);
    }

    public function test_unauthenticated_user_route_redirects_with_an_expired_flag(): void
    {
        $this->get('/_test/auth-required')
            ->assertRedirect(route('login', ['expired' => 1]));
    }

    public function test_unauthenticated_admin_route_redirects_with_an_expired_flag(): void
    {
        $this->get('/_test/admin-required')
            ->assertRedirect(route('login', ['expired' => 1]));
    }
}

class ForceCsrfVerification extends VerifyCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
