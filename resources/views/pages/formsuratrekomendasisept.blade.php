@extends('template');

@section('pageTitle')
Surat Rekomendasi Mengikuti SEP-T
@endsection

@section('mainContent')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Surat Rekomendasi Mengikuti SEP-T</h5>
        </div>

        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="post" action="{{ route('suratrekomendasisept.store') }}" id="formSuratRekomendasiSept" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="nama_mhs">Nama Mahasiswa</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" class="form-control" name="nama_mhs" id="nama_mhs" value="{{ auth()->user()->name }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="npm">NPM</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-number"></i></span>
                        <input type="text" class="form-control" name="npm" id="npm" value="{{ auth()->user()->npm }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="prodi">Program Studi</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-notebook"></i></span>
                        <input type="text" class="form-control" name="prodi" id="prodi" value="{{ auth()->user()->mahasiswa->prodi }}" readonly />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="semester">Semester</label>
                    <div class="input-group input-group-merge">
                        <select name="semester" id="semester" class="select2-icons form-select" required>
                            @foreach(['I (Satu)', 'II (Dua)', 'III (Tiga)', 'IV (Empat)', 'V (Lima)', 'VI (Enam)', 'VII (Tujuh)', 'VIII (Delapan)', 'IX (Sembilan)', 'X (Sepuluh)', 'XI (Sebelas)', 'XII (Dua Belas)', 'XIII (Tiga Belas)', 'XIV (Empat Belas)'] as $semester)
                            <option value="{{ $semester }}" {{ old('semester', auth()->user()->mahasiswa->semester) === $semester ? 'selected' : '' }}>{{ $semester }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="no_hp">Nomor HP</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-phone"></i></span>
                        <input type="text" class="form-control numeric-input" name="no_hp" id="no_hp" value="{{ old('no_hp', auth()->user()->mahasiswa->no_hp) }}" inputmode="numeric" maxlength="15" required />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="inputGroupFile02">Upload Bukti telah membayar UKT (Max: 500 kb)</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-file-upload"></i></span>
                        <input type="file" class="form-control" name="bukti_pembayaran" id="inputGroupFile02" accept="image/*" onchange="previewFile()" required>
                    </div>
                    <div id="preview-container" style="display: none; margin-top: 20px;">
                        <img id="preview-image" alt="Preview Image" style="max-width: 100%; max-height: 200px;">
                        <button type="button" id="remove-btn" onclick="removeFile()" style="display: none; margin-top: 10px;">Remove File</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>
@endsection
