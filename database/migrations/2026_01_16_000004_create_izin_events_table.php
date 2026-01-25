<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_events', function (Blueprint $table) {
            $table->id();

            $table->string('nik', 20)->index();
            $table->unsignedBigInteger('pengajuan_id')->nullable()->index();

            $table->string('client_uuid', 80)->nullable();
            $table->dateTime('captured_at')->nullable();
            $table->dateTime('received_at');
            $table->integer('sync_delay_seconds')->nullable();

            $table->text('user_agent')->nullable();
            $table->string('ip', 45)->nullable();

            $table->string('result_status', 16); // success|error|warning
            $table->string('message', 255)->nullable();
            $table->json('anomaly_flags')->nullable();

            $table->timestamps();

            $table->unique(['nik', 'client_uuid'], 'izin_events_nik_client_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_events');
    }
};

