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
        Schema::create('anggota_observasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('observasi_id');
            $table->string('nama');
            $table->bigInteger('npm');
            $table->string('no_hp');
            $table->foreign('observasi_id')->references('id')->on('surat_observasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_observasi');
    }
};
