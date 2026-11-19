@extends('loginTemplate')

@section('pageTitle')
Verifikasi Identitas Kampus
@endsection

@section('authPage')
<div class="authentication-wrapper authentication-basic px-4 py-5">
    <div class="authentication-inner" style="max-width: 560px;">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-sm-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-label-primary" style="width: 48px; height: 48px; flex: 0 0 48px;">
                        <i class="ti ti-school fs-3"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 fw-semibold">Verifikasi identitas kampus</h3>
                        <p class="text-muted mb-0">Hubungkan akun lama dengan data resmi mahasiswa.</p>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                @if (session('status'))
                    <div class="alert alert-info" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($verification?->status === 'verified')
                    <div class="alert alert-success mb-4">
                        <div class="fw-semibold mb-1"><i class="ti ti-circle-check me-1"></i> Identitas telah terverifikasi</div>
                        <div>{{ $verification->official_name }} · {{ $verification->npm }}</div>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-primary w-100">Lanjut ke dashboard</a>
                @elseif ($verification?->status === 'pending_review')
                    <div class="rounded border p-4 mb-4 bg-light">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-label-warning">Menunggu pemeriksaan</span>
                            <small class="text-muted">Dikirim {{ $verification->submitted_at?->diffForHumans() }}</small>
                        </div>
                        <div class="fw-semibold">{{ $verification->official_name }}</div>
                        <div class="text-muted">NPM {{ $verification->npm }}</div>
                        <p class="small text-muted mb-0 mt-3">Admin akan membandingkan data akun lama dengan database kampus. Data lama belum diubah selama proses ini.</p>
                    </div>
                    <a href="{{ route('logout') }}" class="btn btn-outline-secondary w-100">Keluar</a>
                @else
                    @if ($verification?->status === 'rejected')
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Verifikasi sebelumnya ditolak</div>
                            <div>{{ $verification->review_note ?: 'Silakan periksa NPM dan kirim ulang.' }}</div>
                        </div>
                    @else
                        <div class="alert alert-primary">
                            NPM dan email akun harus sama dengan database kampus. Kedua data ini dikunci agar identitas tidak dapat dialihkan ke akun lain.
                        </div>
                    @endif

                    <form action="{{ route('campus-identity.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="npm" class="form-label fw-medium">NPM akun</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="text" class="form-control" id="npm" value="{{ auth()->user()->npm }}" readonly aria-readonly="true">
                            </div>
                            <div class="form-text">NPM diambil langsung dari akun dan tidak dapat diubah saat verifikasi.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email akun</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="email" class="form-control" id="email" value="{{ auth()->user()->email }}" readonly aria-readonly="true">
                            </div>
                            <div class="form-text">Email harus sama dengan email mahasiswa dari kampus.</div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">Konfirmasi password akun</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="current-password" required autofocus>
                            <div class="form-text">Diperlukan untuk memastikan perubahan dilakukan oleh pemilik akun.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Verifikasi data</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('logout') }}" class="text-muted">Keluar dari akun</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
