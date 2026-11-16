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
        Schema::create('surat_pindah_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_mhs');
            $table->bigInteger('npm');
            $table->enum('prodi', ['Informatika', 'Sistem Informasi']);
            $table->string('no_hp');
            $table->integer('semester');
            $table->string('nama_ortu');
            $table->string('prodi_tujuan');
            $table->string('fakultas_tujuan');
            $table->string('univ_tujuan');
            $table->string('jenis_surat')->default('Surat Pindah Kuliah');
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
        Schema::dropIfExists('surat_pindah_kuliah');
    }
};
