<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetUrl,
        public string $userEmail,
        public int $expiresInMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $replyToAddress = env('MAIL_REPLY_TO_ADDRESS', $fromAddress);
        $replyToName = env('MAIL_REPLY_TO_NAME', 'TU Fasilkom UNSIKA');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [new Address($replyToAddress, $replyToName)],
            subject: 'Reset Password - ' . config('app.name', 'Fasilkom UNSIKA'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'userEmail' => $this->userEmail,
                'expiresInMinutes' => $this->expiresInMinutes,
                'appName' => config('app.name', 'Fasilkom UNSIKA'),
            ],
        );
    }

    public function headers(): Headers
    {
        $appUrl = config('app.url');
        $unsubscribeMail = env('MAIL_REPLY_TO_ADDRESS', config('mail.from.address'));

        return new Headers(
            text: [
                'X-Mailer' => 'Laravel',
                'X-Priority' => '3',
                'List-Unsubscribe' => '<' . $appUrl . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'X-Entity-Type' => 'Transactional',
                'X-Entity-ID' => 'password-reset',
                'Auto-Submitted' => 'auto-generated',
            ],
        );
    }
}
