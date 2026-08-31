@extends('template');

@section('pageTitle')
Surat Pengantar Magang
@endsection

@section('mainContent')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Surat Pengantar Magang</h5>
        </div>

        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="post" action="{{ route('suratpengantarmagang.store') }}" id="formSuratIzinPenelitian" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Nama Mahasiswa</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" class="form-control alphabet-input" name="nama_mhs" id="basic-icon-default-fullname" value="{{ auth()->user()->name }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">NPM</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-company2" class="input-group-text"><i class="ti ti-number"></i></span>
                        <input type="text" id="basic-icon-default-company" name="npm" class="form-control numeric-input npm" value="{{ auth()->user()->npm }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">Prodi</label>
                    <div class="input-group input-group-merge">
                        <select name="prodi" id="select2IconsProdi" class="select2-icons form-select">
                            <option value="Informatika" data-icon="ti ti-notebook">Informatika</option>
                            <option value="Sistem Informasi" data-icon="ti ti-notebook">Sistem Informasi</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col">
                        <label class="form-label" for="basic-icon-default-fullname">Email</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                            <input type="text" class="form-control" name="email" id="basic-icon-default-fullname" value="{{ old('email') }}" required />
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label" for="basic-icon-default-fullname">No. HP</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                            <input type="text" class="form-control" name="no_hp" id="basic-icon-default-fullname" value="{{ old('no_hp') }}" required />
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col">
                        <label class="form-label" for="basic-icon-default-fullname">Tempat Lahir</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                            <input type="text" class="form-control" name="tempat_lahir" id="basic-icon-default-fullname" value="{{ old('tempat_lahir') }}" required />
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label" for="basic-icon-default-fullname">Tanggal Lahir</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                            <input type="date" class="form-control" name="tanggal_lahir" id="basic-icon-default-fullname" value="{{ old('tanggal_lahir') }}" required />
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Agama</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                        <input type="text" class="form-control" name="agama" id="basic-icon-default-fullname" value="{{ old('agama') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Alamat Lengkap</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                        <input type="text" class="form-control" name="alamat" id="basic-icon-default-fullname" value="{{ old('alamat') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Tujuan Surat</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                        <input type="text" class="form-control" name="tujuan_surat" id="basic-icon-default-fullname" placeholder="Cth: Kepala Dinas Perhubungan/HRD PT. Angin Ribut/Manajer Plaza Cafe" value="{{ old('tujuan_surat') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Tujuan Instansi</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-location"></i></span>
                        <input type="text" class="form-control" name="tujuan_instansi" id="basic-icon-default-fullname" placeholder="Cth: Dinas Perhubungan/Plaza Cafe/PT. Angin Ribut" value="{{ old('tujuan_instansi') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Domisili Instansi</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-map-pin"></i></span>
                        <input type="text" class="form-control alphabet-input" name="domisili_instansi" id="basic-icon-default-fullname" placeholder="Cth: Kabupaten Karawang/ Kota Bekasi" value="{{ old('domisili_instansi') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">Upload Bukti telah membayar UKT (Max: 500 kb)</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-company2" class="input-group-text"><i class="ti ti-file-upload"></i></span>
                        <input type="file" class="form-control" name="bukti_pembayaran" id="inputGroupFile02" accept="image/*" onchange="previewFile()" required>
                    </div>
                    <div id="preview-container" style="display: none; margin-top: 20px;">
                        <img id="preview-image" alt="Preview Image" style="max-width: 100%; max-height: 200px;">
                        <button id="remove-btn" onclick="removeFile()" style="display: none; margin-top: 10px;">Remove File</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>


@endsection
