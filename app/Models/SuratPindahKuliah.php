<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPindahKuliah extends Model
{
    use HasFactory;

    protected $table = 'surat_pindah_kuliah';

    protected $fillable = ['nomor_surat', 'nama_mhs', 'npm', 'prodi', 'semester', 'no_hp', 'nama_ortu', 'prodi_tujuan', 'fakultas_tujuan', 'univ_tujuan', 'created_at', 'updated_at'];
}
