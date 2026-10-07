<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['kegiatan', 'karya'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->foreignId('periode_id')->nullable()->constrained('periode')->noActionOnDelete();
                if ($name === 'karya') {
                    $table->foreignId('biro_id')->nullable()->constrained('biro')->noActionOnDelete();
                }
                $table->string('biro_nama', 100)->nullable();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['kegiatan', 'karya'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropConstrainedForeignId('periode_id');
                if ($name === 'karya') {
                    $table->dropConstrainedForeignId('biro_id');
                }
                $table->dropColumn(['biro_nama', 'deleted_at']);
            });
        }
    }
};
