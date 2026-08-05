<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biro', function (Blueprint $table) {
            $table->string('tipe', 20)->default('biro')->after('nama');
        });

        DB::table('biro')->whereNull('tipe')->orWhere('tipe', '')->update(['tipe' => 'biro']);

        if (! DB::table('biro')->where('slug', 'badan-pengurus-harian')->exists()) {
            DB::table('biro')->insert([
                'nama' => 'Badan Pengurus Harian',
                'slug' => 'badan-pengurus-harian',
                'tipe' => 'bph',
                'deskripsi' => 'Pimpinan rayon yang menggerakkan roda organisasi sehari-hari.',
                'warna_aksen' => '#002068',
                'urutan' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('biro', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
