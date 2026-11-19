<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use CanResetPassword, HasApiTokens, HasFactory, Notifiable;

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $previousToken = $this->email_verification_token;

        $this->forceFill([
            'email_verification_token' => Str::random(64),
        ])->saveQuietly();

        try {
            $this->notify(new VerifyEmailNotification);
        } catch (Throwable $exception) {
            $this->forceFill([
                'email_verification_token' => $previousToken,
            ])->saveQuietly();

            throw $exception;
        }
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_token' => null,
        ])->save();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'npm',
        'email',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_email_sent_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function campusIdentityVerification()
    {
        return $this->hasOne(CampusIdentityVerification::class);
    }

    public function hasVerifiedCampusIdentity(): bool
    {
        return $this->campusIdentityVerification?->status === CampusIdentityVerification::STATUS_VERIFIED;
    }
}
