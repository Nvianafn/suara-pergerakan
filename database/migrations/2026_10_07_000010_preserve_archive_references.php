<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REFERENCES = [
        'kepengurusan' => ['anggota_id' => ['anggota', 'cascade'], 'periode_id' => ['periode', 'cascade'], 'biro_id' => ['biro', 'set null']],
        'kegiatan' => ['biro_id' => ['biro', 'set null'], 'created_by' => ['users', 'set null']],
        'karya' => ['anggota_id' => ['anggota', 'set null'], 'created_by' => ['users', 'set null']],
        'kegiatan_foto' => ['kegiatan_id' => ['kegiatan', 'cascade']],
        'users' => ['anggota_id' => ['anggota', 'set null']],
    ];

    private function changeReferences(bool $restore): void
    {
        foreach (self::REFERENCES as $name => $references) {
            $objects = DB::getDriverName() === 'sqlite'
                ? DB::select("SELECT type, name, sql FROM sqlite_master WHERE tbl_name = ? AND type IN ('index', 'trigger') AND sql IS NOT NULL", [$name]) : [];
            Schema::table($name, function (Blueprint $table) use ($references, $restore) {
                foreach ($references as $column => [$parent, $action]) {
                    $table->dropForeign([$column]);
                    $table->foreign($column)->references('id')->on($parent)->onDelete($restore ? $action : 'no action');
                }
            });
            // SQLite table rebuilds do not preserve filtered index predicates or triggers.
            foreach ($objects as $object) {
                DB::statement('DROP '.strtoupper($object->type).' IF EXISTS "'.$object->name.'"');
                DB::statement($object->sql);
            }
        }
    }

    public function up(): void
    {
        $this->changeReferences(false);
        foreach (['kegiatan', 'karya'] as $name) {
            foreach (DB::table($name)->whereNotNull('biro_id')->whereNull('biro_nama')->get(['id', 'biro_id']) as $record) {
                DB::table($name)->where('id', $record->id)->update(['biro_nama' => DB::table('biro')->where('id', $record->biro_id)->value('nama')]);
            }
        }
    }

    public function down(): void
    {
        $this->changeReferences(true);
    }
};
