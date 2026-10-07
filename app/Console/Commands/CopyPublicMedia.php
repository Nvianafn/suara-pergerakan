<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CopyPublicMedia extends Command
{
    protected $signature = 'media:copy-public-to-r2';

    protected $description = 'Salin aset public lokal ke R2 tanpa menghapus file sumber.';

    public function handle(): int
    {
        $source = Storage::disk('public');
        $target = Storage::disk('r2_public');
        $count = 0;
        $files = $source->allFiles();
        foreach ($files as $path) {
            if (preg_match('~^(anggota|pembina)/~i', $path)) {
                $this->error('Foto privat legacy ditemukan. Privatkan foto sebelum menyalin aset publik.');

                return self::FAILURE;
            }
        }
        foreach ($files as $path) {
            if (str_ends_with($path, '.gitignore')) {
                continue;
            }
            if ($target->fileExists($path)) {
                if (hash('sha256', $source->get($path)) !== hash('sha256', $target->get($path))) {
                    $this->error('Key tujuan berbeda dari sumber; penyalinan dihentikan tanpa menimpa.');

                    return self::FAILURE;
                }

                continue;
            }
            $bytes = $source->get($path);
            $target->put($path, $bytes);
            if (hash('sha256', $bytes) !== hash('sha256', $target->get($path))) {
                $this->error('Verifikasi penyalinan gagal.');

                return self::FAILURE;
            }
            $count++;
        }
        $this->info($count.' aset disalin dan diverifikasi; sumber dipertahankan.');

        return self::SUCCESS;
    }
}
