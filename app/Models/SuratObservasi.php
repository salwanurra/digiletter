<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratObservasi extends Model
{
    use HasFactory;

    protected $table = 'surat_observasi';
    protected $fillable = ['nomor_surat', 'nama_mhs', 'prodi', 'tujuan_surat', 'tujuan_instansi', 'domisili_instansi', 'mata_kuliah', 'judul_observasi', 'jenis_surat', 'file_path', 'keterangan', 'status', 'created_at', 'updated_at'];

    public function anggota(): HasMany
    {
        return $this->hasMany(AnggotaObservasi::class, 'observasi_id');
    }
}
