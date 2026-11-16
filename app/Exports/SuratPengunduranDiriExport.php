<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use App\Models\SuratPengunduranDiri;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuratPengunduranDiriExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function headings(): array
    {
        return [
            'Tanggal Masuk',
            'Nomor Surat',
            'Nama Mahasiswa',
            'NPM',
            'Prodi',
            'Alamat',
            'No HP',
            'Semester',
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
            $surat->semester,
            $surat->prodi,
            $surat->alamat,
            $surat->no_hp,
            $surat->jenis_surat,
            Carbon::parse($surat->updated_at)->translatedFormat('j F Y'),
            $surat->keterangan,
            $surat->status,
        ];
    }

    public function collection()
    {
        return SuratPengunduranDiri::select('created_at', 'nomor_surat', 'nama_mhs', 'npm', 'prodi',  'alamat', 'no_hp', 'jenis_surat', 'semester', 'updated_at', 'keterangan', 'status')->get();
    }
}
