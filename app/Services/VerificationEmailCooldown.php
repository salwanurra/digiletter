<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class VerificationEmailCooldown
{
    public function seconds(): int
    {
        return max(1, (int) config('verification.resend_cooldown_seconds', 60));
    }

    public function remaining(User $user): int
    {
        $sentAt = User::query()
            ->whereKey($user->getKey())
            ->value('verification_email_sent_at');

        if (! $sentAt) {
            return 0;
        }

        $availableAt = Carbon::parse($sentAt)->addSeconds($this->seconds());

        return max(0, $availableAt->timestamp - now()->timestamp);
    }

    public function acquire(User $user): bool
    {
        $now = now();
        $cutoff = $now->copy()->subSeconds($this->seconds());

        $acquired = User::query()
            ->whereKey($user->getKey())
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('verification_email_sent_at')
                    ->orWhere('verification_email_sent_at', '<=', $cutoff);
            })
            ->update(['verification_email_sent_at' => $now]) === 1;

        if ($acquired) {
            $user->setAttribute('verification_email_sent_at', $now);
        }

        return $acquired;
    }

    public function markSent(User $user): void
    {
        $sentAt = now();

        User::query()
            ->whereKey($user->getKey())
            ->update(['verification_email_sent_at' => $sentAt]);

        $user->setAttribute('verification_email_sent_at', $sentAt);
    }

    public function clear(User $user): void
    {
        User::query()
            ->whereKey($user->getKey())
            ->update(['verification_email_sent_at' => null]);

        $user->setAttribute('verification_email_sent_at', null);
    }
}
