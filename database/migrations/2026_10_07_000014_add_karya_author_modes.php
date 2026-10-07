<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karya', function (Blueprint $table) {
            $table->string('penulis_tipe', 16)->default('redaksi');
            $table->string('penulis_nama', 150)->nullable();
        });
        DB::table('karya')->whereNotNull('anggota_id')->update(['penulis_tipe' => 'anggota']);
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement("ALTER TABLE karya ADD CONSTRAINT CK_karya_penulis CHECK ((penulis_tipe = 'anggota' AND anggota_id IS NOT NULL AND penulis_nama IS NULL) OR (penulis_tipe = 'nama_bebas' AND anggota_id IS NULL AND penulis_nama IS NOT NULL AND LEN(LTRIM(RTRIM(penulis_nama))) > 0) OR (penulis_tipe IN ('anonim','redaksi') AND anggota_id IS NULL AND penulis_nama IS NULL))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE karya DROP CONSTRAINT CK_karya_penulis');
        }
        Schema::table('karya', fn (Blueprint $table) => $table->dropColumn(['penulis_tipe', 'penulis_nama']));
    }
};
