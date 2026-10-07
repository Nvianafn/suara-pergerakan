<?php

namespace Tests\Feature;

use App\Models\Periode;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class SqlServerConcurrencyTest extends TestCase
{
    public function test_simultaneous_activation_finishes_with_one_active_period_and_intact_archives(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            $this->markTestSkipped('Requires real SQL Server and independent connections.');
        }
        $this->assertSame('suara_pergerakan_test', DB::connection()->getDatabaseName());
        $this->artisan('migrate:fresh')->assertSuccessful();
        DB::purge();
        $this->seed();
        $ids = Periode::pluck('id');
        $before = DB::table('kepengurusan')->count();
        $prefix = sys_get_temp_dir().'/period-race-'.bin2hex(random_bytes(8));
        $processes = [];
        try {
            foreach ($ids->take(2) as $index => $id) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/activate-period.php'), (string) $id, $prefix.'-'.$index, $prefix.'-go'], base_path(), ['APP_ENV' => 'testing', 'DB_DATABASE' => 'suara_pergerakan_test']);
                $process->setTimeout(30)->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 15;
            while (! (file_exists($prefix.'-0') && file_exists($prefix.'-1')) && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertFileExists($prefix.'-0');
            $this->assertFileExists($prefix.'-1');
            touch($prefix.'-go');
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getOutput().$process->getErrorOutput());
            }
            DB::purge();
            $this->assertSame(1, Periode::where('is_aktif', true)->count());
            $this->assertSame($ids->count(), Periode::count());
            $this->assertSame($before, DB::table('kepengurusan')->count());
        } finally {
            RefreshDatabaseState::$migrated = false;
            foreach ($processes as $process) {
                $process->stop();
            }
            foreach (glob($prefix.'*') as $file) {
                unlink($file);
            }
        }
    }
}
