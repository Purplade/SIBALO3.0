<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_events', function (Blueprint $table) {
            $table->id();

            $table->string('nik', 20)->index();
            $table->unsignedBigInteger('absensi_id')->nullable()->index();

            // Idempotency key from client (prevents replay / duplicate sync)
            $table->string('client_uuid', 80)->nullable();

            // Client-side capture time vs server receive time
            $table->dateTime('captured_at')->nullable();
            $table->dateTime('received_at');
            $table->integer('sync_delay_seconds')->nullable();

            // Best-effort context
            $table->string('event_type', 16)->nullable(); // in|out|unknown
            $table->text('lokasi')->nullable();
            $table->integer('radius_meters')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip', 45)->nullable();

            // Result summary (for audit trail)
            $table->string('result_status', 16); // success|error|warning
            $table->string('result_tag', 32)->nullable(); // in|out|radius|holiday|...
            $table->string('message', 255)->nullable();
            $table->json('anomaly_flags')->nullable();

            $table->timestamps();

            $table->unique(['nik', 'client_uuid'], 'absensi_events_nik_client_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_events');
    }
};

