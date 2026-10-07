<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Periode extends Model
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('nama')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    protected $table = 'periode';

    protected $fillable = [
        'nama', 'tahun_mulai', 'tahun_selesai', 'is_aktif', 'tema', 'deskripsi',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function kepengurusan(): HasMany
    {
        return $this->hasMany(Kepengurusan::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function pembina(): BelongsToMany
    {
        return $this->belongsToMany(Pembina::class, 'periode_pembina')->withPivot(['id', 'urutan'])->withTimestamps();
    }
}
