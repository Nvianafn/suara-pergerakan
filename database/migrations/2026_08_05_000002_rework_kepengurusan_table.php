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

        if (DB::getDriverName() === 'sqlsrv') {
            foreach (DB::select("SELECT name FROM sys.check_constraints WHERE parent_object_id = OBJECT_ID('kepengurusan') AND (parent_column_id = COLUMNPROPERTY(OBJECT_ID('kepengurusan'), 'level', 'ColumnId') OR definition LIKE '%level%')") as $constraint) {
                DB::statement('ALTER TABLE kepengurusan DROP CONSTRAINT ['.str_replace(']', ']]', $constraint->name).']');
            }
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
