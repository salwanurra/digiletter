@extends('admin.template')

@section('pageTitle')
Verifikasi Identitas
@endsection

@section('mainContentAdmin')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <div class="text-muted small fw-medium text-uppercase mb-1">Rekonsiliasi akun lama</div>
            <h3 class="fw-semibold mb-1">Verifikasi identitas mahasiswa</h3>
            <p class="text-muted mb-0">Bandingkan data akun dengan sumber resmi sebelum menyetujui perubahan.</p>
        </div>
        <span class="badge bg-label-warning fs-6 px-3 py-2">
            {{ $verifications->where('status', 'pending_review')->count() }} menunggu di halaman ini
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-column gap-3">
        @forelse ($verifications as $verification)
            @php
                $statusClasses = [
                    'pending_review' => 'bg-label-warning',
                    'verified' => 'bg-label-success',
                    'rejected' => 'bg-label-danger',
                ];
                $statusLabels = [
                    'pending_review' => 'Menunggu review',
                    'verified' => 'Terverifikasi',
                    'rejected' => 'Ditolak',
                ];
                $identityMatches = (string) $verification->old_npm === (string) $verification->npm
                    && (string) $verification->old_email === (string) $verification->official_email;
            @endphp
            <article class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-4">
                        <div>
                            <span class="badge {{ $statusClasses[$verification->status] ?? 'bg-label-secondary' }} mb-2">{{ $statusLabels[$verification->status] ?? $verification->status }}</span>
                            <div class="small text-muted">Permintaan #{{ $verification->id }} · {{ $verification->submitted_at?->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="text-md-end">
                            <div class="small text-muted">Kemiripan nama</div>
                            <div class="fs-4 fw-semibold">{{ number_format($verification->name_similarity ?? 0, 0) }}%</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="rounded border p-3 h-100">
                                <div class="small text-muted fw-medium text-uppercase mb-3"><i class="ti ti-history me-1"></i> Data akun lama</div>
                                <div class="mb-3">
                                    <div class="small text-muted">Nama</div>
                                    <div class="fw-semibold">{{ $verification->old_name ?: '-' }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted">NPM</div>
                                    <div class="font-monospace">{{ $verification->old_npm ?: '-' }}</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Email akun</div>
                                    <div>{{ $verification->old_email ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rounded border border-primary p-3 h-100 bg-label-primary">
                                <div class="small text-primary fw-medium text-uppercase mb-3"><i class="ti ti-school me-1"></i> Data resmi kampus</div>
                                <div class="mb-3">
                                    <div class="small text-muted">Nama</div>
                                    <div class="fw-semibold">{{ $verification->official_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted">NPM</div>
                                    <div class="font-monospace fw-semibold">{{ $verification->npm }}</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Email kampus</div>
                                    <div>{{ $verification->official_email ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($verification->match_reasons)
                        <div class="small text-muted mb-3">
                            @foreach ($verification->match_reasons as $reason)
                                <span class="me-3"><i class="ti ti-check text-success me-1"></i>{{ $reason }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($verification->status === 'pending_review')
                        <form method="POST" class="border-top pt-3">
                            @csrf
                            @if (! $identityMatches)
                                <div class="alert alert-danger py-2 mb-3">
                                    <i class="ti ti-lock me-1"></i>
                                    Tidak dapat disetujui karena NPM atau email tidak sama dengan database kampus.
                                </div>
                            @endif
                            <label for="note-{{ $verification->id }}" class="form-label">Catatan keputusan</label>
                            <textarea class="form-control mb-3" id="note-{{ $verification->id }}" name="review_note" rows="2" maxlength="1000" placeholder="Wajib diisi jika permintaan ditolak"></textarea>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" formaction="{{ route('admin.campus-identities.approve', $verification) }}" class="btn btn-success" @disabled(! $identityMatches)>
                                    <i class="ti ti-check me-1"></i> Setujui dan terapkan
                                </button>
                                <button type="submit" formaction="{{ route('admin.campus-identities.reject', $verification) }}" class="btn btn-outline-danger">
                                    <i class="ti ti-x me-1"></i> Tolak permintaan
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="border-top pt-3 small text-muted">
                            Diproses {{ $verification->reviewed_at?->format('d M Y, H:i') ?? $verification->verified_at?->format('d M Y, H:i') }}
                            @if ($verification->reviewer) oleh {{ $verification->reviewer->name }} @endif
                            @if ($verification->review_note) · {{ $verification->review_note }} @endif
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-user-check fs-1 text-muted"></i>
                    <h5 class="mt-3 mb-1">Belum ada permintaan verifikasi</h5>
                    <p class="text-muted mb-0">Permintaan user lama akan muncul di halaman ini.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $verifications->links() }}</div>
</div>
@endsection
