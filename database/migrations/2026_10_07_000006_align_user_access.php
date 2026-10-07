<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeRole(['super_admin', 'admin', 'admin_biro']);
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('biro_id')->nullable()->constrained('biro')->noActionOnDelete();
            $table->boolean('is_active')->default(true);
        });
        if (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $operation) {
                DB::statement('CREATE TRIGGER users_biro_'.strtolower($operation)." BEFORE $operation ON users WHEN NEW.role = 'admin_biro' AND NEW.biro_id IS NULL BEGIN SELECT RAISE(ABORT, 'admin_biro requires biro_id'); END");
            }
        } elseif (DB::getDriverName() === 'sqlsrv') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT CK_users_biro CHECK (role <> 'admin_biro' OR biro_id IS NOT NULL)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS users_biro_insert');
            DB::statement('DROP TRIGGER IF EXISTS users_biro_update');
        } elseif (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT CK_users_biro');
        }
        DB::table('users')->where('role', 'admin_biro')->update(['role' => 'admin']);
        $this->changeRole(['super_admin', 'admin']);
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('biro_id');
            $table->dropColumn('is_active');
        });
    }

    private function changeRole(array $roles): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            Schema::table('users', fn (Blueprint $table) => $table->enum('role', $roles)->default('admin')->change());

            return;
        }

        foreach (DB::select("SELECT name FROM sys.check_constraints WHERE parent_object_id = OBJECT_ID('users') AND (parent_column_id = COLUMNPROPERTY(OBJECT_ID('users'), 'role', 'ColumnId') OR definition LIKE '%role%')") as $constraint) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT ['.str_replace(']', ']]', $constraint->name).']');
        }
        $allowed = implode(',', array_map(fn ($role) => "N'".$role."'", $roles));
        DB::statement("ALTER TABLE users ADD CONSTRAINT CK_users_role CHECK (role IN ($allowed))");
    }
};
