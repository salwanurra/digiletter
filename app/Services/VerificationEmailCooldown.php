<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class VerificationEmailCooldown
{
    public function seconds(): int
    {
        return max(1, (int) config('verification.resend_cooldown_seconds', 60));
    }

    public function remaining(User $user): int
    {
        return RateLimiter::availableIn($this->key($user));
    }

    public function acquire(User $user): bool
    {
        return RateLimiter::attempt(
            $this->key($user),
            1,
            fn () => true,
            $this->seconds()
        ) !== false;
    }

    public function markSent(User $user): void
    {
        RateLimiter::hit($this->key($user), $this->seconds());
    }

    public function clear(User $user): void
    {
        RateLimiter::clear($this->key($user));
    }

    private function key(User $user): string
    {
        return 'verification-email:'.$user->getKey();
    }
}
