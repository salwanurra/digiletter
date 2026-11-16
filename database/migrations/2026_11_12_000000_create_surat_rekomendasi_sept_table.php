<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_rekomendasi_sept', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_mhs');
            $table->bigInteger('npm');
            $table->enum('prodi', ['Informatika', 'Sistem Informasi']);
            $table->string('semester');
            $table->string('no_hp');
            $table->string('jenis_surat')->default('Surat Rekomendasi Mengikuti SEP-T');
            $table->string('keterangan')->nullable();
            $table->string('status')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_rekomendasi_sept');
    }
};
