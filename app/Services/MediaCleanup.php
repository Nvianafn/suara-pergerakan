<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaCleanup
{
    public static function enqueue(string $disk, ?string $path): void
    {
        if ($path) {
            DB::table('media_cleanup')->insert(['disk' => $disk, 'path' => $path, 'created_at' => now()]);
        }
    }

    public static function run(): int
    {
        $remaining = 0;
        foreach (DB::table('media_cleanup')->orderBy('id')->cursor() as $item) {
            try {
                if (Storage::disk($item->disk)->delete($item->path)) {
                    DB::table('media_cleanup')->where('id', $item->id)->delete();
                } else {
                    $remaining++;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $remaining++;
            }
        }

        return $remaining;
    }
}
