<?php

namespace App\Console\Commands;

use App\Models\Anggota;
use App\Services\PrivateImageService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PrivatizeMemberPhotos extends Command
{
    protected $signature = 'media:privatize-member-photos';

    protected $description = 'Re-encode legacy public member photos into private storage and remove public copies';

    public function handle(PrivateImageService $images): int
    {
        $public = Storage::disk('public');
        foreach (Anggota::withTrashed()->whereNotNull('foto')->cursor() as $anggota) {
            $old = $anggota->foto;
            if (! $public->exists($old)) {
                continue;
            }
            $file = new UploadedFile($public->path($old), basename($old), test: true);
            $new = $images->store($file, 'anggota/'.$anggota->id);
            try {
                $anggota->foto = $new;
                $anggota->save();
            } catch (\Throwable $exception) {
                $images->delete($new);
                throw $exception;
            }
            if (! $public->delete($old)) {
                $this->error('Tidak dapat menghapus salinan publik: '.$old);

                return self::FAILURE;
            }
            $this->info('Foto anggota #'.$anggota->id.' dipindahkan ke storage privat.');
        }

        return self::SUCCESS;
    }
}
