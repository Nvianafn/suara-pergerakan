<?php

namespace App\Models\Concerns;

use App\Services\HtmlSanitizer;

trait HasSafePublishedContent
{
    public static function bootHasSafePublishedContent(): void
    {
        static::saving(function ($content) {
            $field = $content->getTable() === 'karya' ? 'konten' : 'deskripsi';
            $content->{$field} = app(HtmlSanitizer::class)->clean($content->{$field});
            if ($content->exists && $content->getRawOriginal('published_at') !== null) {
                $content->published_at = $content->getRawOriginal('published_at');
            } else {
                $content->published_at = $content->status === 'published' ? now() : null;
            }
        });
    }

    public function getSafeHtmlAttribute(): ?string
    {
        // Protect legacy/imported rows as well as normal model writes.
        return app(HtmlSanitizer::class)->clean($this->getTable() === 'karya' ? $this->konten : $this->deskripsi);
    }
}
