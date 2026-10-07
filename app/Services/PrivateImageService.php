<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PrivateImageService
{
    public function delete(?string $path): void
    {
        if ($path) {
            MediaCleanup::enqueue('r2_private', $path);
            MediaCleanup::run();
        }
    }

    public function store(UploadedFile $file, string $directory): string
    {
        $image = (new ImageManager(new Driver))->read($file->getRealPath());
        $image->scaleDown(width: 1200, height: 1200);
        $path = $directory.'/'.Str::random(32).'.webp';
        Storage::disk('r2_private')->put($path, (string) $image->toWebp(82));

        return $path;
    }
}
