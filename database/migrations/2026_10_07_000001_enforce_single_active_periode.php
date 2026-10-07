<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['sqlite', 'sqlsrv'], true)) {
            DB::statement('CREATE UNIQUE INDEX UQ_periode_aktif ON periode (is_aktif) WHERE is_aktif = 1');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('DROP INDEX UQ_periode_aktif ON periode');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX UQ_periode_aktif');
        }
    }
};
