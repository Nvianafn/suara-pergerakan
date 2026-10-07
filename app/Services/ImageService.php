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
     * Returns the path relative to the public disk (use asset('storage/'.$path)).
     */
    public function store(UploadedFile $file, string $dir, int $maxWidth = 1600): string
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $path = trim($dir, '/').'/'.Str::random(24).'.webp';
        Storage::disk('public')->put($path, (string) $image->toWebp(82));

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
