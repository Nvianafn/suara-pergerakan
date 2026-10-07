<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PublicMedia
{
    public static function disk(): string
    {
        return config('filesystems.public_media_disk', 'public');
    }

    public static function url(string $path): string
    {
        return Storage::disk(self::disk())->url($path);
    }
}
