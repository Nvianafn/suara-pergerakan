<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kepengurusan extends Model
{
    protected $table = 'kepengurusan';

    protected $fillable = [
        'anggota_id', 'periode_id', 'biro_id', 'biro_nama', 'level', 'jabatan', 'is_ketua', 'urutan',
    ];

    protected $casts = [
        'is_ketua' => 'boolean',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function biro(): BelongsTo
    {
        return $this->belongsTo(Biro::class);
    }

    public function scopeBph($query)
    {
        return $query->where('level', 'bph');
    }

    protected static function booted(): void
    {
        static::saving(function (self $pengurus) {
            if (! $pengurus->level) {
                $pengurus->level = $pengurus->biro?->tipe === 'bph' || ! $pengurus->biro_id
                    ? 'bph' : ($pengurus->is_ketua ? 'ketua_biro' : 'anggota_biro');
            }
            if ($pengurus->level === 'bph') {
                $pengurus->biro_id = null;
            }
            if (! $pengurus->exists || $pengurus->isDirty('biro_id')) {
                $pengurus->biro_nama = $pengurus->biro_id ? Biro::find($pengurus->biro_id)?->nama : null;
            }
            $pengurus->is_ketua = $pengurus->level === 'ketua_biro';
        });
    }
}
