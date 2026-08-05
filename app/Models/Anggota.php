<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    protected $table = 'anggota';

    protected $fillable = [
        'nim', 'nama_lengkap', 'nama_panggilan', 'angkatan',
        'fakultas', 'prodi', 'no_hp', 'email', 'foto', 'bio', 'status',
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
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
