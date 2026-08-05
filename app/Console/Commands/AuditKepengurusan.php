<?php

namespace App\Console\Commands;

use App\Models\Kepengurusan;
use Illuminate\Console\Command;

class AuditKepengurusan extends Command
{
    protected $signature = 'kepengurusan:audit {--periode= : ID periode tertentu (opsional)}';

    protected $description = 'Cek data kepengurusan: pengurus tanpa struktur/bagian.';

    public function handle(): int
    {
        $query = Kepengurusan::with(['anggota', 'periode', 'biro']);

        if ($periodeId = $this->option('periode')) {
            $query->where('periode_id', $periodeId);
        }

        $pengurus = $query->get();
        $tanpaUnit = $pengurus->filter(fn ($k) => ! $k->biro_id);

        $this->info('Total pengurus: '.$pengurus->count());
        $this->info('Periode terisi: '.$pengurus->pluck('periode_id')->unique()->count());

        if ($tanpaUnit->isEmpty()) {
            $this->info('Semua pengurus sudah punya struktur/bagian. ✓');

            return self::SUCCESS;
        }

        $this->warn($tanpaUnit->count().' pengurus tanpa struktur/bagian (perlu diperbaiki lewat admin):');
        foreach ($tanpaUnit as $k) {
            $this->warn("  #{$k->id} {$k->jabatan} ({$k->anggota?->nama_lengkap}) — periode #{$k->periode_id}");
        }

        return self::SUCCESS;
    }
}
