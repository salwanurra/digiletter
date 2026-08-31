@extends('template');

@section('pageTitle')
Surat Observasi
@endsection

<script src="https://cdn.tailwindcss.com"></script>

@section('mainContent')
<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Form Surat Observasi</h5>
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

            <form method="post" action="{{ route('suratobservasi.store') }}" id="formSuratObservasi">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="prodi">Prodi</label>
                    <div class="input-group input-group-merge">
                        <select name="prodi" id="prodi" class="select2-icons form-select prodi">
                            <option value="Informatika" data-icon="ti ti-notebook">Informatika</option>
                            <option value="Sistem Informasi" data-icon="ti ti-notebook">Sistem Informasi</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tujuan_surat">Tujuan Surat</label>
                    <div class="input-group input-group-merge">
                        <span id="tujuan_surat2" class="input-group-text"><i class="ti ti-drag-drop"></i></span>
                        <input type="text" class="form-control" name="tujuan_surat" id="tujuan_surat" placeholder="Contoh: HRD/Manajer/Owner" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="tujuan_instansi">Tujuan Instansi</label>
                    <div class="input-group input-group-merge">
                        <span id="tujuan_instansi2" class="input-group-text"><i class="ti ti-location"></i></span>
                        <input type="text" id="tujuan_instansi" name="tujuan_instansi" class="form-control" placeholder="Contoh: Dinas Tenaga Kerja/Toko ABC" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="domisili_instansi">Kota Tujuan</label>
                    <div class="input-group input-group-merge">
                        <span id="domisili_instansi2" class="input-group-text"><i class="ti ti-map-pin"></i></span>
                        <input type="text" id="domisili_instansi" name="domisili_instansi" class="form-control" placeholder="Contoh: Kabupaten Karawang" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mata_kuliah">Tugas dari Mata Kuliah</label>
                    <div class="input-group input-group-merge">
                        <span id="mata_kuliah2" class="input-group-text"><i class="ti ti-book"></i></span>
                        <input type="text" id="mata_kuliah" name="mata_kuliah" class="form-control" placeholder="Contoh: Pengantar Teknologi Informasi" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="judul_observasi">Judul/Tema Observasi</label>
                    <div class="input-group input-group-merge">
                        <span id="judul_observasi2" class="input-group-text"><i class="ti ti-book"></i></span>
                        <input type="text" id="judul_observasi" name="judul_observasi" class="form-control" placeholder="Contoh: bagaimana SOP Pembuatan KTP dan Akta Kelahiran" />
                    </div>
                </div>
                <div class="mb-3 flex flex-col" x-data="{ items: @js($anggota ?? [['nama' => '', 'npm' => '', 'no_hp' => '']]) }">
                    <label class="form-label" for="basic-icon-default-company">Anggota Observasi</label>
                    <template x-for="(item, index) in items" x-bind:key="index">
                        <div class="flex items-center gap-2 my-1">
                            <div>
                                <span x-text="index + 1 +'.'"></span>
                            </div>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2" class="input-group-text">Nama</span>
                                <input type="text" id="basic-icon-default-company" x-model="item.nama" :name="`anggota[${index}][nama]`" class="form-control nama" placeholder="Nama anggota" />
                            </div>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2" class="input-group-text">NPM</span>
                                <input type="text" id="basic-icon-default-company" x-model="item.npm" :name="`anggota[${index}][npm]`" class="form-control numeric-input npm" placeholder="NPM Anggota" />
                            </div>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2" class="input-group-text">Nomor WhatsApp</span>
                                <input type="text" id="basic-icon-default-company" x-model="item.no_hp" :name="`anggota[${index}][no_hp]`" class="form-control numeric-input no_hp" placeholder="08" />
                            </div>
                            <div>
                                <button type="button" class="p-2 border rounded-sm" x-on:click="items.splice(index, 1)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>

                            </div>
                        </div>
                    </template>
                    <div class="my-2">
                        <button type="button" class="flex items-center gap-1 bg-white !border !border-gray-500 text-gray-700 text-sm p-1.5 rounded-md hover:!bg-gray-400 hover:text-white" x-on:click="items.push({ nama: '', npm: '', no_hp: ''})">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Tambah Anggota</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>


@endsection