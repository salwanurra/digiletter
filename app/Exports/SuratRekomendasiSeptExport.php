<?php

namespace App\Exports;

use App\Models\SuratRekomendasiSept;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuratRekomendasiSeptExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Tanggal Masuk',
            'Nomor Surat',
            'Nama Mahasiswa',
            'NPM',
            'Program Studi',
            'Semester',
            'Nomor HP',
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
            "'".$surat->npm,
            $surat->prodi,
            $surat->semester,
            $surat->no_hp,
            $surat->jenis_surat,
            Carbon::parse($surat->updated_at)->translatedFormat('j F Y'),
            $surat->keterangan,
            $surat->status,
        ];
    }

    public function collection()
    {
        return SuratRekomendasiSept::select(
            'created_at',
            'nomor_surat',
            'nama_mhs',
            'npm',
            'prodi',
            'semester',
            'no_hp',
            'jenis_surat',
            'updated_at',
            'keterangan',
            'status'
        )->get();
    }
}
