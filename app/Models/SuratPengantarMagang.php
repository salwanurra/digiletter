<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPengantarMagang extends Model
{
    use HasFactory;

    protected $table = 'surat_pengantar_magang';
    protected $fillable = ['nomor_surat','nama_mhs', 'npm', 'prodi', 'tempat_lahir', 'tanggal_lahir', 'agama', 'alamat', 'email', 'no_hp', 'tujuan_surat', 'tujuan_instansi', 'domisili_instansi', 'keterangan', 'status', 'file_path', 'bukti_pembayaran'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
