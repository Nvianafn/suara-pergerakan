<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pembina extends Model
{
    protected $table = 'pembina';

    protected $fillable = ['anggota_id', 'nama_lengkap', 'keterangan', 'setuju_publikasi'];

    protected $casts = ['anggota_id' => 'integer', 'setuju_publikasi' => 'boolean', 'setuju_publikasi_at' => 'datetime'];

    protected $hidden = ['anggota_id', 'foto', 'keterangan', 'setuju_publikasi_at'];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function periode(): BelongsToMany
    {
        return $this->belongsToMany(Periode::class, 'periode_pembina')->withPivot(['id', 'urutan'])->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (self $pembina) {
            if ($pembina->setuju_publikasi && (! $pembina->exists || $pembina->isDirty('setuju_publikasi'))) {
                $pembina->setuju_publikasi_at = now();
            }
        });
    }
}
