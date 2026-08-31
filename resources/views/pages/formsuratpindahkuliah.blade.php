@extends('template');

@section('pageTitle')
Surat Pindah Kuliah
@endsection

@section('mainContent')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Surat Pindah Kuliah</h5>
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

            <form method="post" action="{{ route('suratpindahkuliah.store') }}" id="formSuratPindahKuliah" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Nama Mahasiswa</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" class="form-control" name="nama_mhs" id="basic-icon-default-fullname" value="{{ auth()->user()->name }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">NPM</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-company2" class="input-group-text"><i class="ti ti-number"></i></span>
                        <input type="text" id="basic-icon-default-company" name="npm" class="form-control numeric-input" value="{{ auth()->user()->npm }}" readonly />
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
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">Semester</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-company2" class="input-group-text"><i class="ti ti-number"></i></span>
                        <input type="text" id="basic-icon-default-company" name="semester" class="form-control numeric-input" placeholder="Cth: 6" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">No HP</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-company2" class="input-group-text"><i class="ti ti-numbers"></i></span>
                        <input type="text" id="basic-icon-default-company" name="no_hp" class="form-control" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-fullname">Nama Orang Tua/Wali</label>
                    <div class="input-group input-group-merge">
                        <span id="basic-icon-default-message2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" id="basic-icon-default-message" name="nama_ortu" class="form-control" placeholder="" required></input>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="prodi_tujuan">Prodi Tujuan (Pindah)</label>
                    <div class="input-group input-group-merge">
                        <span id="prodi_tujuan2" class="input-group-text"><i class="ti ti-notebook"></i></span>
                        <input type="text" id="prodi_tujuan" name="prodi_tujuan" class="form-control" placeholder="" required></input>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="fakultas_tujuan">Fakultas Tujuan (Pindah)</label>
                    <div class="input-group input-group-merge">
                        <span id="fakultas_tujuan2" class="input-group-text"><i class="ti ti-notebook"></i></span>
                        <input type="text" id="fakultas_tujuan" name="fakultas_tujuan" class="form-control" placeholder="" required></input>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="univ_tujuan">Universitas Tujuan (Pindah)</label>
                    <div class="input-group input-group-merge">
                        <span id="univ_tujuan2" class="input-group-text"><i class="ti ti-notebook"></i></span>
                        <input type="text" id="univ_tujuan" name="univ_tujuan" class="form-control" placeholder="" required></input>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>


@endsection