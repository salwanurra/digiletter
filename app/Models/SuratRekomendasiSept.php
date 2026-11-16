<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRekomendasiSept extends Model
{
    use HasFactory;

    protected $table = 'surat_rekomendasi_sept';

    protected $fillable = [
        'nomor_surat',
        'nama_mhs',
        'npm',
        'prodi',
        'semester',
        'no_hp',
        'jenis_surat',
        'keterangan',
        'status',
        'file_path',
        'bukti_pembayaran',
    ];
}
