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
            $table->boolean('is_ketua')->default(false)->after('jabatan');
        });

        DB::table('kepengurusan')->where('level', 'ketua_biro')->update(['is_ketua' => true]);

        $bphId = DB::table('biro')->where('tipe', 'bph')->value('id');
        if ($bphId) {
            DB::table('kepengurusan')
                ->where('level', 'bph')
                ->update(['biro_id' => $bphId]);
        }

        Schema::table('kepengurusan', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }

    public function down(): void
    {
        Schema::table('kepengurusan', function (Blueprint $table) {
            $table->enum('level', ['bph', 'ketua_biro', 'anggota_biro'])->default('bph')->after('jabatan');
        });

        $bphId = DB::table('biro')->where('tipe', 'bph')->value('id');
        if ($bphId) {
            DB::table('kepengurusan')
                ->where('biro_id', $bphId)
                ->update(['level' => 'bph']);
        }
        DB::table('kepengurusan')
            ->where('is_ketua', true)
            ->where('level', 'bph')
            ->update(['level' => 'ketua_biro']);
    }
};
