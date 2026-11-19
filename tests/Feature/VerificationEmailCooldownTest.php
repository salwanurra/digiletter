<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Services\VerificationEmailCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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

        Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('warning');

        Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);
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

    public function test_first_visit_sends_one_email_and_immediately_displays_the_countdown(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Email verifikasi telah dikirim')
            ->assertSee('Kirim ulang dalam');

        Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);

        $this->actingAs($user)->get(route('verification.notice'))->assertOk();

        Notification::assertSentToTimes($user, VerifyEmailNotification::class, 1);
    }

    public function test_sending_a_new_email_invalidates_the_previous_link(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $cooldown = app(VerificationEmailCooldown::class);

        $this->actingAs($user)->get(route('verification.notice'));
        $oldToken = (string) $user->fresh()->email_verification_token;
        $oldUrl = $this->verificationUrl($user, $oldToken, now()->addMinutes(15));

        $cooldown->clear($user);
        $this->actingAs($user)->post(route('verification.send'));

        $newToken = (string) $user->fresh()->email_verification_token;
        $this->assertNotSame($oldToken, $newToken);

        $this->actingAs($user)->get($oldUrl)->assertForbidden();

        $newUrl = $this->verificationUrl($user, $newToken, now()->addMinutes(15));
        $this->actingAs($user)->get($newUrl)->assertRedirect(route('home'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->email_verification_token);
    }

    public function test_verification_link_expires_after_its_time_limit(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('verification.notice'));
        $token = (string) $user->fresh()->email_verification_token;
        $url = $this->verificationUrl($user, $token, now()->addMinute());

        $this->travel(2)->minutes();

        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    private function verificationUrl(User $user, string $token, \DateTimeInterface $expiresAt): string
    {
        return URL::temporarySignedRoute('verification.verify', $expiresAt, [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
            'token' => $token,
        ]);
    }
}
