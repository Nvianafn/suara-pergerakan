<?php

namespace App\Console\Commands;

use App\Models\Kepengurusan;
use Illuminate\Console\Command;

class RepairKepengurusanLevels extends Command
{
    protected $signature = 'kepengurusan:repair-levels {--dry-run : Tampilkan yang akan diperbaiki tanpa menyimpan}';

    protected $description = 'Perbaiki data kepengurusan yang level-nya salah (biro terisi tapi level bph).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $fixed = 0;
        $manual = [];

        foreach (Kepengurusan::with(['anggota', 'biro'])->get() as $k) {
            if ($k->level === 'bph' && $k->biro_id) {
                $level = str_contains(strtolower((string) $k->jabatan), 'ketua') ? 'ketua_biro' : 'anggota_biro';

                if ($dryRun) {
                    $this->line("  [akan diperbaiki] #{$k->id} {$k->jabatan} -> {$level} ({$k->biro?->nama})");
                } else {
                    $k->update(['level' => $level]);
                    $this->line("  [diperbaiki] #{$k->id} {$k->jabatan} -> {$level} ({$k->biro?->nama})");
                }

                $fixed++;
            } elseif ($k->level !== 'bph' && ! $k->biro_id) {
                $manual[] = $k;
            }
        }

        $this->info($dryRun
            ? "Selesai (dry-run): {$fixed} record akan diperbaiki."
            : "Selesai: {$fixed} record diperbaiki.");

        if ($manual) {
            $this->warn(count($manual).' record anggota biro tanpa biro (perlu dicek manual):');
            foreach ($manual as $k) {
                $this->warn("  #{$k->id} {$k->jabatan} ({$k->anggota?->nama_lengkap})");
            }
        }

        $suspicious = Kepengurusan::selectRaw('periode_id, COUNT(*) as total')
            ->groupBy('periode_id')
            ->havingRaw('COUNT(*) > 4')
            ->where('level', 'bph')
            ->get();

        if ($suspicious->isNotEmpty()) {
            $this->warn('Periode berikut berisi banyak pengurus berlevel BPH (kemungkinan data salah):');
            foreach ($suspicious as $row) {
                $this->warn("  periode #{$row->periode_id}: {$row->total} pengurus semua level bph");
            }
        }

        return self::SUCCESS;
    }
}
