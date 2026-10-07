<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();
            $table->string('aksi', 50);
            $table->string('subjek_tipe', 50);
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->string('ringkasan')->nullable();
            $table->text('perubahan')->nullable();
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_aktivitas');
    }
};
