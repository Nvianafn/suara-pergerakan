<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', fn (Blueprint $table) => $table->dateTime('published_at')->nullable());
        // Legacy records have no first-publication history; created_at is a deterministic fallback.
        DB::table('kegiatan')->where('status', 'published')->update(['published_at' => DB::raw('created_at')]);
        DB::table('karya')->where('status', 'published')->whereNull('published_at')->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('kegiatan', fn (Blueprint $table) => $table->dropColumn('published_at'));
    }
};
