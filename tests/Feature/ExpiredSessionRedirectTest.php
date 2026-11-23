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
    }

    public function test_expired_web_session_redirects_to_login_and_persists_the_message(): void
    {
        $response = $this->post('/_test/expired-session');

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sesi Anda telah berakhir. Silakan masuk kembali.');
    }

    public function test_authenticated_user_returns_to_the_previous_page_with_a_message(): void
    {
        $this->be(new User([
            'name' => 'Pengguna',
            'email' => 'pengguna@example.test',
        ]));

        $response = $this->from(route('home'))->post('/_test/expired-session');

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Halaman telah kedaluwarsa. Silakan coba kembali.');
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
}

class ForceCsrfVerification extends VerifyCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
