@extends('loginTemplate')

@section('pageTitle')
Verifikasi Email
@endsection

@section('authPage')
<div class="authentication-wrapper authentication-basic px-4">
    <div class="authentication-inner py-4">
        <div class="card">
            <div class="card-body">
                <h3 class="mb-2 fw-bold">Verifikasi email Anda</h3>
                <p class="mb-4">
                    Kami mengirim tautan verifikasi ke <strong>{{ auth()->user()->email }}</strong>.
                    Buka tautan tersebut sebelum menggunakan DigiLetter.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        Tautan verifikasi baru telah dikirim. Periksa kotak masuk dan folder spam.
                    </div>
                @endif

                @if ($initialEmailSent)
                    <div class="alert alert-success">
                        Email verifikasi telah dikirim. Tautan berlaku selama {{ config('auth.verification.expire') }} menit.
                    </div>
                @endif

                @if (session('warning') || $deliveryWarning)
                    <div class="alert alert-warning">
                        {{ session('warning') ?: $deliveryWarning }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" id="resend-verification-form">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-primary w-100 mb-2"
                        id="resend-verification-button"
                        data-retry-after="{{ $retryAfter }}"
                        @disabled($retryAfter > 0)
                    >
                        <span id="resend-verification-label" style="font-variant-numeric: tabular-nums;">
                            {{ $retryAfter > 0 ? 'Kirim ulang dalam '.gmdate('i:s', $retryAfter) : 'Kirim Ulang Email Verifikasi' }}
                        </span>
                    </button>
                    <p class="small text-muted text-center mb-3" id="resend-verification-help" aria-live="polite">
                        @if ($retryAfter > 0)
                            Email baru dapat dikirim setelah waktu tunggu selesai.
                        @else
                            Belum menerima email? Periksa folder spam sebelum mengirim ulang.
                        @endif
                    </p>
                </form>

                <div class="text-center">
                    <a href="{{ route('logout') }}">Keluar dan kembali ke halaman masuk</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('resend-verification-button');
        const label = document.getElementById('resend-verification-label');
        const help = document.getElementById('resend-verification-help');
        let remaining = Number(button.dataset.retryAfter || 0);

        if (remaining <= 0) {
            return;
        }

        const endsAt = Date.now() + remaining * 1000;

        let timer = null;

        const updateCountdown = function () {
            remaining = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));

            if (remaining === 0) {
                button.disabled = false;
                label.textContent = 'Kirim Ulang Email Verifikasi';
                help.textContent = 'Belum menerima email? Periksa folder spam sebelum mengirim ulang.';
                if (timer !== null) {
                    window.clearInterval(timer);
                }
                return;
            }

            const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
            const seconds = String(remaining % 60).padStart(2, '0');
            label.textContent = `Kirim ulang dalam ${minutes}:${seconds}`;
        };

        updateCountdown();
        timer = window.setInterval(updateCountdown, 1000);
    });
</script>
@endsection
