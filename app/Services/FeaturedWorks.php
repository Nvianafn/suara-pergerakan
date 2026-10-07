<?php

namespace App\Services;

use App\Models\Karya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeaturedWorks
{
    public function validateSelection(array $data, ?Karya $work = null): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            // Transaction-owned mutex also serializes concurrent CMS selections.
            DB::statement("DECLARE @result int; EXEC @result = sp_getapplock @Resource = 'karya-featured', @LockMode = 'Exclusive', @LockOwner = 'Transaction', @LockTimeout = 10000; IF @result < 0 THROW 50001, 'Could not lock featured works', 1;");
        }
        if (! ($data['is_featured'] ?? false)) {
            return;
        }
        if ($data['status'] !== 'published') {
            throw ValidationException::withMessages(['is_featured' => 'Karya Pilihan harus berstatus published.']);
        }
        if (Karya::published()->where('is_featured', true)->when($work, fn ($query) => $query->where('id', '!=', $work->id))->count() >= 6) {
            throw ValidationException::withMessages(['is_featured' => 'Maksimal enam Karya Pilihan. Lepas salah satu pilihan terlebih dahulu.']);
        }
    }

    public function forHome(): Collection
    {
        $selected = Karya::with('anggota')->published()->where('is_featured', true)->orderByDesc('published_at')->orderByDesc('id')->take(6)->get();
        if ($selected->count() < 4) {
            $fallback = Karya::with('anggota')->published()->whereNotIn('id', $selected->modelKeys())->orderByDesc('published_at')->orderByDesc('id')->take(4 - $selected->count())->get();
            $selected = $selected->concat($fallback);
        }

        return $selected;
    }
}
