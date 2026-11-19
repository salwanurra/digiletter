@extends('template');

@section('pageTitle')
User Settings
@endsection

@section('mainContent')

<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Pengaturan /</span>
    Data Diri
</h4>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-pills flex-column flex-md-row mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="javascript:void(0);"><i class="ti-xs ti ti-users me-1"></i>
                    Data Diri</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('user.settingSecurity', ['id'=> auth()->user()->id]) }}"><i class="ti-xs ti ti-lock me-1"></i> Akun</a>
            </li>

            <li class="nav-item mx-3">
                {{-- validasi error di session --}}
                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>

                {{-- validasi error $request->validate() --}}
                @elseif($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </li>
        </ul>

        <!-- Lengkapi Profile -->
        <div class="card card-action mb-4">
            <div class="card-header align-items-center">
                <h5 class="card-action-title mb-0">Lengkapi Profile</h5>
            </div>
            <form method="post" action="{{ route('user.lengkapiprofile',  ['id' => auth()->id()]) }}" enctype="multipart/form-data">
                @csrf
                <!-- Account -->
                <!--<div class="card-body">-->
                <!--    <div class="d-flex align-items-start align-items-sm-center gap-4">-->
                <!--        <img src="{{ asset('laravel/storage/app/public/foto-mahasiswa/' . $user->mahasiswa->foto) ?? '' }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />-->
                <!--        <div class="button-wrapper">-->
                <!--            <span class="d-none d-sm-block">Upload new photo</span>-->
                <!--            <i class="ti ti-upload d-block d-sm-none"></i>-->
                <!--            <input type="file" class="form-control" name="foto" id="inputGroupFile02" onchange="previewFile()" required>-->
                <!--            <div id="preview-container" style="display: none; margin-top: 20px;">-->
                <!--                <img id="preview-image" alt="Preview Image" style="max-width: 100%; max-height: 200px;">-->
                <!--                <button id="remove-btn" onclick="removeFile()" style="display: none; margin-top: 10px;">Remove File</button>-->
                <!--            </div>-->
                <!--            <div class="text-muted">-->
                <!--                Mengizinkan format JPG, SVG, atau PNG. Ukuran maksimal 1MB.-->
                <!--            </div>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                <hr class="my-0" />
                <div class="card-body pb-0">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Nama Mahasiswa</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-fullname2" class="input-group-text"></span>
                            <input type="text" class="form-control" id="basic-icon-default-fullname" value="{{ $user->name }}" readonly />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-company">NPM</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2" class="input-group-text"></span>
                            <input type="text" id="basic-icon-default-company" class="form-control" value="{{ $user->npm }}" readonly />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-company">Semester</label>
                        <div class="input-group input-group-merge">
                            <select name="semester" class="select2-icons form-select">
                                @foreach (['I (Satu)', 'II (Dua)', 'III (Tiga)', 'IV (Empat)', 'V (Lima)', 'VI (Enam)', 'VII (Tujuh)', 'VIII (Delapan)', 'IX (Sembilan)', 'X (Sepuluh)', 'XI (Sebelas)', 'XII (Dua Belas)', 'XIII (Tiga Belas)', 'XIV (Empat Belas)'] as $semester)
                                    <option value="{{ $semester }}" @selected(old('semester', $user->mahasiswa?->semester) === $semester)>{{ $semester }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Prodi</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-prodi" class="input-group-text"></span>
                            <input type="text" id="basic-icon-default-prodi" class="form-control" value="{{ $user->mahasiswa?->prodi }}" readonly />
                        </div>
                    </div>
                    <!-- <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-company">Domisili</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2" class="input-group-text"></span>
                            <input type="text" id="basic-icon-default-company" name="domisili" class="form-control" value="{{ old('domisili', $user->mahasiswa?->domisili) }}" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Jenis Kelamin</label>
                        <div class="input-group input-group-merge">
                            <select name="jenis_kelamin" class="select2-icons form-select">
                                <option value="Laki-laki" @selected(old('jenis_kelamin', $user->mahasiswa?->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin', $user->mahasiswa?->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Status Mahasiswa</label>
                        <input type="text" id="basic-icon-default-company" class="form-control" value="{{ $user->mahasiswa?->status }}" readonly />
                    </div> -->
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-phone">No HP</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2" class="input-group-text"></span>
                            <input type="text" inputmode="numeric" id="basic-icon-default-phone" name="no_hp" class="form-control phone-mask numeric-input npm" value="{{ old('no_hp', $user->mahasiswa?->no_hp) }}" />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>
@endsection
