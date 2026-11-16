<?php

namespace App\Notifications;

use App\Mail\ResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): ResetPasswordMail
    {
        $appUrl = rtrim(config('app.url'), '/');

        $recipientEmail = $notifiable->getEmailForPasswordReset();
        $recipientName = $notifiable->name ?? null;

        $resetUrl = $appUrl . '/reset-password/' . $this->token . '?email=' . urlencode($recipientEmail);

        $expiresInMinutes = (int) config(
            'auth.passwords.' . config('auth.defaults.passwords', 'users') . '.expire',
            60
        );

        return (new ResetPasswordMail(
            resetUrl: $resetUrl,
            userEmail: $recipientEmail,
            expiresInMinutes: $expiresInMinutes,
        ))->to($recipientEmail, $recipientName);
    }
}
