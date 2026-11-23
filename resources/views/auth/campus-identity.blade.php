@extends('loginTemplate')

@section('pageTitle')
Verifikasi Identitas Mahasiswa
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
                        <h3 class="mb-1 fw-semibold">Verifikasi identitas mahasiswa</h3>
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
                            Masukkan NPM Anda. NPM, nama, dan email akun akan disamakan otomatis dengan database kampus.
                        </div>
                    @endif

                    <form action="{{ route('campus-identity.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="npm" class="form-label fw-medium">NPM</label>
                            <input type="text" inputmode="numeric" maxlength="13" class="form-control @error('npm') is-invalid @enderror" id="npm" name="npm" value="{{ old('npm', auth()->user()->npm) }}" required>
                            <div class="form-text">Isi dengan NPM 13 angka. Data kampus di bawah akan diperbarui otomatis.</div>
                        </div>
                        <div class="rounded border p-3 mb-3 bg-light" id="campus-preview" aria-live="polite">
                            <div class="small mb-1 fw-bold">Data dari database kampus</div>
                            <div class="" id="preview-name">{{ $preview['name'] ?? '-' }}</div>
                            <div class="small mt-2">Email yang terdaftar pada akun Digiletter</div>
                            <div id="preview-old-email">{{ auth()->user()->email }}</div>
                            <div class="small mt-2">Email dari database kampus (tujuan verifikasi ulang)</div>
                            <div class="fw-semibold " id="preview-email">{{ $preview['email'] ?? 'NPM tidak ditemukan pada data kampus.' }}</div>
                            <div class="small mt-1 mb-0 fw-semibold" id="preview-note">
                                @if (($preview['email'] ?? null) && strtolower($preview['email']) !== strtolower(auth()->user()->email))
                                    Email akun akan diganti ke email dari database kampus dan link verifikasi dikirim ke sana.
                                @elseif ($preview)
                                    Email akun sudah sama dengan database kampus.
                                @endif
                            </div>
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
<script>
(function () {
    var input = document.getElementById('npm');
    if (!input) return;
    var oldEmail = @json(auth()->user()->email);
    var nameEl = document.getElementById('preview-name');
    var emailEl = document.getElementById('preview-email');
    var noteEl = document.getElementById('preview-note');
    var url = @json(route('campus-identity.lookup'));
    var timer;

    function render(data, message) {
        if (!data) {
            nameEl.textContent = '-';
            emailEl.textContent = message;
            noteEl.textContent = '';
            return;
        }
        nameEl.textContent = data.name;
        emailEl.textContent = data.email;
        noteEl.textContent = data.email.toLowerCase() === String(oldEmail).toLowerCase()
            ? 'Email akun sudah sama dengan email kampus.'
            : 'Email akun akan diganti ke email kampus dan link verifikasi dikirim ke sana.';
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        var npm = input.value.replace(/\D/g, '');
        input.value = npm;
        if (npm.length !== 13) {
            render(null, 'Masukkan 13 digit NPM.');
            return;
        }
        timer = setTimeout(function () {
            fetch(url + '?npm=' + encodeURIComponent(npm), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function (d) { render(d); })
                .catch(function () { render(null, 'NPM tidak ditemukan pada data kampus.'); });
        }, 350);
    });
})();
</script>
@endsection
