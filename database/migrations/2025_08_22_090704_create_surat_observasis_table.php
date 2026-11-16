<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_observasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mhs');
            $table->string('nomor_surat')->nullable();
            $table->string('tujuan_surat');
            $table->string('tujuan_instansi');
            $table->string('domisili_instansi');
            $table->string('judul_observasi');
            $table->string('mata_kuliah');
            $table->string('jenis_surat')->default('Surat Observasi');
            $table->string('file_path')->nullable();
            $table->string('keterangan')->nullable()->default(null);
            $table->string('status')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_observasi');
    }
};