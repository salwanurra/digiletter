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
        Schema::create('surat_pengantar_magang', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_mhs');
            $table->bigInteger('npm');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('agama');
            $table->string('alamat');
            $table->string('email');
            $table->enum('prodi',['Informatika', 'Sistem Informasi']);
            $table->string('no_hp');
            $table->string('tujuan_surat');
            $table->string('tujuan_instansi');
            $table->string('domisili_instansi');
            $table->string('jenis_surat')->default('Surat Pengantar Magang');
            $table->string('keterangan')->nullable()->default(null);
            $table->string('status')->nullable()->default(null);
            $table->string('file_path')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengantar_magang');
    }
};
