<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Pembina;
use App\Models\Periode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SqlServerChecksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDriverName() !== 'sqlsrv') {
            $this->markTestSkipped('Verifies SQL Server CHECK constraints directly.');
        }
    }

    public function test_database_rejects_biro_admin_without_biro(): void
    {
        $this->expectException(QueryException::class);
        DB::table('users')->insert(['name' => 'Invalid', 'email' => 'invalid@example.test', 'password' => 'hash', 'role' => 'admin_biro', 'biro_id' => null]);
    }

    public function test_database_rejects_unknown_account_role(): void
    {
        $this->expectException(QueryException::class);
        DB::table('users')->insert(['name' => 'Invalid', 'email' => 'invalid@example.test', 'password' => 'hash', 'role' => 'owner']);
    }

    public function test_database_rejects_bph_with_biro(): void
    {
        $this->seed();
        $this->expectException(QueryException::class);
        DB::table('kepengurusan')->insert(['anggota_id' => Anggota::first()->id, 'periode_id' => Periode::first()->id, 'jabatan' => 'Uji constraint', 'level' => 'bph', 'biro_id' => Biro::first()->id]);
    }

    public function test_database_rejects_biro_level_without_biro(): void
    {
        $this->seed();
        $this->expectException(QueryException::class);
        DB::table('kepengurusan')->insert(['anggota_id' => Anggota::first()->id, 'periode_id' => Periode::first()->id, 'jabatan' => 'Uji constraint', 'level' => 'anggota_biro', 'biro_id' => null]);
    }

    public function test_database_rejects_duplicate_pembina_placement(): void
    {
        $this->seed();
        $profile = Pembina::create(['nama_lengkap' => 'Pembina uji']);
        $placement = ['pembina_id' => $profile->id, 'periode_id' => Periode::first()->id];
        DB::table('periode_pembina')->insert($placement);
        $this->expectException(QueryException::class);
        DB::table('periode_pembina')->insert($placement);
    }

    public function test_database_blocks_deletion_of_placed_pembina(): void
    {
        $this->seed();
        $profile = Pembina::create(['nama_lengkap' => 'Pembina arsip']);
        DB::table('periode_pembina')->insert(['pembina_id' => $profile->id, 'periode_id' => Periode::first()->id]);
        $this->expectException(QueryException::class);
        DB::table('pembina')->where('id', $profile->id)->delete();
    }
}
