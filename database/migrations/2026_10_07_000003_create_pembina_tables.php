<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->nullable()->constrained('anggota')->noActionOnDelete();
            $table->string('nama_lengkap', 150);
            $table->string('foto')->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->boolean('setuju_publikasi')->default(false);
            $table->dateTime('setuju_publikasi_at')->nullable();
            $table->timestamps();
        });
        if (in_array(DB::getDriverName(), ['sqlite', 'sqlsrv'], true)) {
            DB::statement('CREATE UNIQUE INDEX UQ_pembina_anggota ON pembina (anggota_id) WHERE anggota_id IS NOT NULL');
        }
        Schema::create('periode_pembina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembina_id')->constrained('pembina')->noActionOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->noActionOnDelete();
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['pembina_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_pembina');
        Schema::dropIfExists('pembina');
    }
};
