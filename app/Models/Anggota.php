<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anggota extends Model
{
    use SoftDeletes;

    protected $hidden = ['nim', 'no_hp', 'email', 'foto', 'bio', 'setuju_publikasi_at'];

    protected $casts = ['setuju_publikasi' => 'boolean', 'setuju_publikasi_at' => 'datetime'];

    protected $table = 'anggota';

    protected $fillable = [
        'nim', 'nama_lengkap', 'nama_panggilan', 'angkatan',
        'fakultas', 'prodi', 'no_hp', 'email', 'foto', 'bio', 'status', 'setuju_publikasi',
    ];

    public function kepengurusan(): HasMany
    {
        return $this->hasMany(Kepengurusan::class);
    }

    public function karya(): HasMany
    {
        return $this->hasMany(Karya::class);
    }

    public function initial(): string
    {
        return strtoupper(mb_substr($this->nama_lengkap, 0, 1));
    }

    public function getFotoUrlAttribute(): ?string
    {
        $internal = auth()->user()?->is_active && in_array(auth()->user()?->role, ['admin', 'super_admin'], true);

        return $this->foto && ($internal || $this->setuju_publikasi) ? route('media.anggota', $this->id) : null;
    }

    protected static function booted(): void
    {
        static::saving(function (self $anggota) {
            if ($anggota->setuju_publikasi && (! $anggota->exists || $anggota->isDirty('setuju_publikasi'))) {
                $anggota->setuju_publikasi_at = now();
            }
        });
    }
}
