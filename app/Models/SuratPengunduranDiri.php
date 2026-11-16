<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPengunduranDiri extends Model
{
    use HasFactory;

    protected $table = 'surat_pengunduran_diri';

    protected $fillable = ['nomor_surat', 'nama_mhs', 'npm', 'prodi','alamat','no_hp', 'periode', 'tahun_akademik', 'bukti_pembayaran', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
