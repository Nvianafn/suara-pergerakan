<?php

namespace App\Models;

use App\Services\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $all = Cache::rememberForever('settings.all', function () {
            return static::pluck('value', 'key')->toArray();
        });

        $aliases = ['instagram' => 'sosmed_instagram', 'facebook' => 'sosmed_facebook', 'youtube' => 'sosmed_youtube'];
        $canonical = $aliases[$key] ?? $key;
        $legacy = array_search($canonical, $aliases, true);

        return $all[$canonical] ?? ($legacy ? ($all[$legacy] ?? $default) : $default);
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    public static function imageUrl(string $key, string $fallback): string
    {
        $path = static::get($key);

        return $path ? Storage::disk('public')->url($path) : asset($fallback);
    }

    protected static function booted(): void
    {
        static::saving(function (self $setting) {
            if (in_array($setting->key, ['tentang_deskripsi', 'tentang_sejarah', 'misi'], true)) {
                $setting->value = app(HtmlSanitizer::class)->clean($setting->value);
            }
        });
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
