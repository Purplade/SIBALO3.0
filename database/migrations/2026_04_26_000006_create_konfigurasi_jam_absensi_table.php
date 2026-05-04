<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('konfigurasi_jam_absensi', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk_mulai')->default('06:00:00');
            $table->time('jam_pulang_mulai')->default('12:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_jam_absensi');
    }
};
