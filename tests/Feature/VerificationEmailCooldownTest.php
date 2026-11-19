<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VerificationEmailCooldown;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationEmailCooldownTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_is_blocked_until_the_cooldown_finishes(): void
    {
        Notification::fake();
        config(['verification.resend_cooldown_seconds' => 60]);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('warning');

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_verification_page_displays_a_disabled_countdown_after_sending(): void
    {
        Notification::fake();
        config(['verification.resend_cooldown_seconds' => 60]);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post(route('verification.send'));

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Kirim ulang dalam')
            ->assertSee('disabled', false);
    }

    public function test_only_one_request_can_acquire_the_resend_slot(): void
    {
        config(['verification.resend_cooldown_seconds' => 60]);

        $user = User::factory()->unverified()->create();
        $cooldown = app(VerificationEmailCooldown::class);

        $this->assertTrue($cooldown->acquire($user));
        $this->assertFalse($cooldown->acquire($user));

        $cooldown->clear($user);

        $this->assertTrue($cooldown->acquire($user));
    }
}
