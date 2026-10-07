<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Resize (scale down only), convert to WebP, and store on the public disk.
     * Returns an object key on the configured public media disk.
     */
    public function store(UploadedFile $file, string $dir, int $maxWidth = 1600): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $path = trim($dir, '/').'/'.Str::random(24).'.webp';
        Storage::disk(PublicMedia::disk())->put($path, (string) $image->toWebp(82));

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            MediaCleanup::enqueue(PublicMedia::disk(), $path);
            MediaCleanup::run();
        }
    }
}
