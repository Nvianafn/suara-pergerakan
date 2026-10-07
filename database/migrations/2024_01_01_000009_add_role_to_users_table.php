<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin'])->default('admin')->after('password');
            $table->foreignId('anggota_id')->nullable()->after('role')->constrained('anggota')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            foreach (DB::select("SELECT name FROM sys.check_constraints WHERE parent_object_id = OBJECT_ID('users') AND (parent_column_id = COLUMNPROPERTY(OBJECT_ID('users'), 'role', 'ColumnId') OR definition LIKE '%role%')") as $constraint) {
                DB::statement('ALTER TABLE users DROP CONSTRAINT ['.str_replace(']', ']]', $constraint->name).']');
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('anggota_id');
            $table->dropColumn('role');
        });
    }
};
