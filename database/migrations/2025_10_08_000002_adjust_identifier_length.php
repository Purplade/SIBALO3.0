<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tables = ['pegawai', 'absensi', 'pengajuan_izin'];
        foreach ($tables as $table) {
            $hasNip = DB::select("SHOW COLUMNS FROM `{$table}` LIKE 'nip'");
            $hasNik = DB::select("SHOW COLUMNS FROM `{$table}` LIKE 'nik'");
            if (!empty($hasNip)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `nip` VARCHAR(18)");
            }
            if (!empty($hasNik)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `nik` VARCHAR(18)");
            }
        }
    }

    public function down(): void
    {
        // Revert to VARCHAR(16) as a sensible default; adjust if your prior size differs
        $tables = ['pegawai', 'absensi', 'pengajuan_izin'];
        foreach ($tables as $table) {
            $hasNip = DB::select("SHOW COLUMNS FROM `{$table}` LIKE 'nip'");
            $hasNik = DB::select("SHOW COLUMNS FROM `{$table}` LIKE 'nik'");
            if (!empty($hasNip)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `nip` VARCHAR(16)");
            }
            if (!empty($hasNik)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `nik` VARCHAR(16)");
            }
        }
    }
};


