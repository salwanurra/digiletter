<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaObservasi extends Model
{
    use HasFactory;

    protected $table = 'anggota_observasi';
    protected $fillable = ['nama', 'npm', 'no_hp', 'observasi_id'];
    public $timestamps = false;

    public function observasi(): BelongsTo
    {
        return $this->belongsTo(SuratObservasi::class, 'observasi_id');
    }
}
