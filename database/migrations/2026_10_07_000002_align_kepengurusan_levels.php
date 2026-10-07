<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kepengurusan', function (Blueprint $table) {
            $table->dropUnique(['anggota_id', 'periode_id', 'jabatan']);
            $table->string('level', 16)->default('bph');
            $table->string('biro_nama', 100)->nullable();
        });
        foreach (DB::table('kepengurusan')->get() as $row) {
            $biro = DB::table('biro')->find($row->biro_id);
            $isBph = ! $biro || $biro->tipe === 'bph';
            DB::table('kepengurusan')->where('id', $row->id)->update([
                'level' => $isBph ? 'bph' : ($row->is_ketua ? 'ketua_biro' : 'anggota_biro'),
                'biro_id' => $isBph ? null : $row->biro_id,
                'biro_nama' => $isBph ? null : $biro->nama,
            ]);
        }
        if (in_array(DB::getDriverName(), ['sqlite', 'sqlsrv'], true)) {
            DB::statement('CREATE UNIQUE INDEX UQ_kepengurusan_bph ON kepengurusan (anggota_id, periode_id, jabatan) WHERE biro_id IS NULL');
            DB::statement('CREATE UNIQUE INDEX UQ_kepengurusan_biro ON kepengurusan (anggota_id, periode_id, jabatan, biro_id) WHERE biro_id IS NOT NULL');
        }
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement("ALTER TABLE kepengurusan ADD CONSTRAINT CK_kepengurusan_level CHECK (level IN ('bph','ketua_biro','anggota_biro'))");
            DB::statement("ALTER TABLE kepengurusan ADD CONSTRAINT CK_kepengurusan_biro CHECK ((level = 'bph' AND biro_id IS NULL) OR (level IN ('ketua_biro','anggota_biro') AND biro_id IS NOT NULL))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE kepengurusan DROP CONSTRAINT CK_kepengurusan_level, CK_kepengurusan_biro');
        }
        foreach (['UQ_kepengurusan_bph', 'UQ_kepengurusan_biro'] as $index) {
            if (DB::getDriverName() === 'sqlsrv') {
                DB::statement("DROP INDEX $index ON kepengurusan");
            } elseif (DB::getDriverName() === 'sqlite') {
                DB::statement("DROP INDEX $index");
            }
        }
        $bph = DB::table('biro')->where('tipe', 'bph')->value('id');
        DB::table('kepengurusan')->where('level', 'bph')->update(['biro_id' => $bph]);
        Schema::table('kepengurusan', function (Blueprint $table) {
            $table->dropColumn(['level', 'biro_nama']);
            $table->unique(['anggota_id', 'periode_id', 'jabatan']);
        });
    }
};
