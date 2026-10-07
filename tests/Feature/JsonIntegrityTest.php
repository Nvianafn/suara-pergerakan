<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class JsonIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public static function columns(): array
    {
        return [['karya', 'tags'], ['riwayat_aktivitas', 'perubahan']];
    }

    #[DataProvider('columns')]
    public function test_sql_server_rejects_invalid_json(string $table, string $column): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            $this->markTestSkipped('SQL Server JSON constraint verification.');
        }
        $this->seed();
        if ($table === 'riwayat_aktivitas') {
            DB::table($table)->insert(['aksi' => 'test', 'subjek_tipe' => 'test', 'created_at' => now()]);
        }
        $this->expectException(QueryException::class);
        DB::table($table)->update([$column => 'not-json']);
    }
}
