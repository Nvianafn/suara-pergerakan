<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periode', fn (Blueprint $table) => $table->string('slug', 270)->nullable());
        $used = [];
        foreach (DB::table('periode')->orderBy('id')->get(['id', 'nama']) as $periode) {
            $base = Str::slug($periode->nama) ?: 'periode';
            $slug = $base;
            $suffix = 1;
            while (in_array($slug, $used, true)) {
                $slug = $base.'-'.++$suffix;
            }
            DB::table('periode')->where('id', $periode->id)->update(['slug' => $slug]);
            $used[] = $slug;
        }
        Schema::table('periode', function (Blueprint $table) {
            $table->string('slug', 270)->nullable(false)->change();
            $table->unique('slug');
        });
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS UQ_periode_aktif');
            DB::statement('CREATE UNIQUE INDEX UQ_periode_aktif ON periode (is_aktif) WHERE is_aktif = 1');
        }
    }

    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS UQ_periode_aktif');
            DB::statement('CREATE UNIQUE INDEX UQ_periode_aktif ON periode (is_aktif) WHERE is_aktif = 1');
        }
    }
};
