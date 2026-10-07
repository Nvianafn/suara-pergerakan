<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE karya ADD CONSTRAINT CK_karya_tags_json CHECK (tags IS NULL OR ISJSON(tags) = 1)');
            DB::statement('ALTER TABLE riwayat_aktivitas ADD CONSTRAINT CK_activity_changes_json CHECK (perubahan IS NULL OR ISJSON(perubahan) = 1)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE karya DROP CONSTRAINT CK_karya_tags_json');
            DB::statement('ALTER TABLE riwayat_aktivitas DROP CONSTRAINT CK_activity_changes_json');
        }
    }
};
