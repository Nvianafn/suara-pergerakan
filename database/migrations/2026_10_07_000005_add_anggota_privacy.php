<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->boolean('setuju_publikasi')->default(false);
            $table->dateTime('setuju_publikasi_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('anggota', fn (Blueprint $table) => $table->dropColumn(['setuju_publikasi', 'setuju_publikasi_at', 'deleted_at']));
    }
};
