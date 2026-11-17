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

                @if (session('warning'))
                    <div class="alert alert-warning">
                        {{ session('warning') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <div class="text-center">
                    <a href="{{ route('logout') }}">Keluar dan kembali ke halaman masuk</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
