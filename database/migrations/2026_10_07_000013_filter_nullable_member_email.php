<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        Schema::table('anggota', fn (Blueprint $table) => $table->dropUnique(['email']));
        DB::statement('CREATE UNIQUE INDEX anggota_email_unique ON anggota (email) WHERE email IS NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        Schema::table('anggota', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique('email');
        });
    }
};
