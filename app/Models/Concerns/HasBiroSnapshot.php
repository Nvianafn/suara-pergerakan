<?php

namespace App\Models\Concerns;

use App\Models\Biro;

trait HasBiroSnapshot
{
    public static function bootHasBiroSnapshot(): void
    {
        static::saving(function ($content) {
            if (! $content->exists || $content->isDirty('biro_id')) {
                $content->biro_nama = Biro::find($content->biro_id)?->nama;
            }
        });
    }

    public function getBiroLabelAttribute(): ?string
    {
        return $this->biro_nama ?? $this->biro?->nama;
    }
}
