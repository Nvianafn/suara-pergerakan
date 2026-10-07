<?php

namespace App\Services;

use App\Models\Kegiatan;
use Illuminate\Validation\ValidationException;

class GalleryLimits
{
    public function validate(?Kegiatan $kegiatan, int $incoming, array $removed = []): void
    {
        $remaining = $kegiatan
            ? $kegiatan->foto()->whereNotIn('id', $removed)->count()
            : 0;
        if ($remaining + $incoming > 20) {
            throw ValidationException::withMessages(['foto' => 'Maksimal 20 foto galeri per kegiatan, termasuk foto yang sudah tersimpan.']);
        }
    }
}
