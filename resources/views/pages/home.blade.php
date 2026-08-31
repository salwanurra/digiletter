@extends('template');

@section('pageTitle')
Home
@endsection

@section('mainContent')
<div class="col-lg mb-4">
    <div class="container-xxl">
        <h4 class="fw-bold mb-4"><span class="text-muted fw-light">Home /</span> Pembuatan Surat</h4>
        <!-- Examples -->
        <div class="row mb-5">
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Izin Penelitian</h5>
                        <p class="card-text">
                            Surat Izin Penelitian diperlukan ketika seorang mahasiswa akan melakukan penelitian, baik untuk tugas akhir atau proyek khusus. Surat ini berisi permohonan izin kepada pihak terkait.
                        </p>
                        <br>
                        <br>
                        <a href="{{ route('suratizinpenelitian.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Keterangan Aktif Kuliah</h5>
                        <p class="card-text">
                            Surat Keterangan Aktif digunakan untuk menyatakan bahwa seseorang masih aktif sebagai mahasiswa di suatu perguruan tinggi.
                        </p>
                        <br>
                        <br>
                        <br>
                        <a href="{{ route('suratketeranganaktif.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Surat Keterangan Aktif Kuliah (Orang Tua PNS)</h5>
                        <p class="card-text">
                            Surat Keterangan Aktif (Orang Tua PNS) khusus diperuntukkan bagi mahasiswa yang orang tuanya merupakan Pegawai Negeri Sipil (PNS). Surat ini umumnya diperlukan untuk keperluan administrasi tertentu.
                        </p>
                        <br>
                        <a href="{{ route('suratketeranganaktifortupns.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Pengantar Magang</h5>
                        <p class="card-text">
                            Surat Pengantar Magang diperlukan ketika seorang mahasiswa akan melakukan magang. Surat ini berisi permohonan izin kepada pihak terkait.
                        </p>
                        <br>
                        <br>
                        <br>
                        <a href="{{ route('suratpengantarmagang.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Pengantar Bebas Pustaka</h5>
                        <p class="card-text">
                            Surat Bebas Pustaka diperlukan sebagai bukti bahwa seorang mahasiswa telah mengembalikan semua buku perpustakaan yang dipinjam.
                        </p>
                        <br>
                        <br>
                        <br>
                        <a href="{{ route('suratbebaspustaka.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Pengajuan Cuti</h5>
                        <p class="card-text">
                            Surat pengajuan cuti digunakan untuk memohon izin cuti dari kegiatan akademis. Surat ini umumnya berisi alasan yang jelas dan mendalam mengenai keperluan cuti, seperti masalah kesehatan, kebutuhan pribadi, atau keterlibatan dalam proyek atau aktivitas luar kurikulum yang signifikan.
                        </p>
                        <a href="{{ route('suratpengajuancuti.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Pengunduran Diri</h5>
                        <p class="card-text">
                            Lampiran <strong>persyaratan</strong> pengunduran diri: <br>
                            1. Surat pengunduran diri yang ditandatangani oleh mahasiswa dan orang tua/wali mahasiswa. <br>
                            2. Kartu Tanda Mahasiswa (KTM) asli. <br>
                            3. Transkrip nilai. <br>
                            4. Surat bebas tunggakan dari PANDU. <br>
                            5. Surat keterangan bebas pustaka dari perpustakaan (buat surat pengantar bebas pustaka di Digiletter sebelum ke perpustakaan).
                        </p>
                        <a href="{{ route('suratpengundurandiri.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Observasi</h5>
                        <p class="card-text">
                            Surat observasi ini berisi permohonan dari mahasiswa Fakultas Ilmu Komputer Universitas Singaperbangsa Karawang untuk melakukan observasi/wawancara di luar kampus sebagai bagian dari tugas mata kuliah.
                        </p>
                        <a href="{{ route('suratobservasi.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Surat Pindah Kuliah</h5>
                        <p class="card-text">
                            Surat pindah kuliah adalah surat resmi yang diajukan mahasiswa kepada pihak perguruan tinggi asal maupun perguruan tinggi tujuan sebagai permohonan perpindahan studi.
                        </p>
                        <a href="{{ route('suratpindahkuliah.create', ['id' => auth()->id()]) }}" class="btn btn-secondary">Buat Surat</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

<script>
    // reload halaman otomatis setiap 2 jam
    function refreshPage() {
        location.reload(true);
    }
    setInterval(refreshPage, 7200000); // 2 jam = 7200000 milidetik

</script>
