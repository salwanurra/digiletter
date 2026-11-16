<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use App\Models\SuratPengantarMagang;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuratPengantarMagangExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Tanggal Masuk',
            'Nomor Surat',
            'Nama Mahasiswa',
            'NPM',
            'Tempat, Tanggal Lahir',
            'Agama',
            'Alamat',
            'Email',
            'Prodi',
            'Nomor Kontak',
            'Tujuan Surat',
            'Tujuan Instansi',
            'Domisili Instansi',
            'Jenis Surat',
            'Tanggal Approve',
            'Keterangan',
            'Status',
        ];
    }

    public function map($surat): array
    {
        return [
            Carbon::parse($surat->created_at)->translatedFormat('j F Y'),
            $surat->nomor_surat,
            $surat->nama_mhs,
            "'" . $surat->npm,
            $surat->tempat_lahir . ', ' . $surat->tanggal_lahir,
            $surat->agama,
            $surat->alamat,
            $surat->email,
            $surat->prodi,
            $surat->no_hp,
            $surat->tujuan_surat,
            $surat->tujuan_instansi,
            $surat->domisili_instansi,
            $surat->jenis_surat,
            Carbon::parse($surat->updated_at)->translatedFormat('j F Y'),
            $surat->keterangan,
            $surat->status,
        ];
    }

    public function collection()
    {
        return SuratPengantarMagang::select('created_at','nomor_surat','nama_mhs', 'npm', 'tempat_lahir', 'tanggal_lahir', 'agama', 'alamat', 'email', 'prodi', 'no_hp', 'tujuan_surat', 'tujuan_instansi', 'domisili_instansi', 'jenis_surat', 'updated_at','keterangan', 'status')->get();
    }
}
