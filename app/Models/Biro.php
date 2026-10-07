<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Biro extends Model
{
    use HasSlug;

    protected $attributes = ['is_aktif' => true];

    protected $casts = ['is_aktif' => 'boolean'];

    protected $table = 'biro';

    protected $fillable = [
        'nama', 'slug', 'tipe', 'deskripsi', 'logo', 'warna_aksen', 'urutan', 'is_aktif',
    ];

    public function isBph(): bool
    {
        return $this->tipe === 'bph';
    }

    public function scopeUnitBph($query)
    {
        return $query->where('tipe', 'bph');
    }

    public function scopeUnitBiro($query)
    {
        return $query->where('tipe', 'biro');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nama')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function kepengurusan(): HasMany
    {
        return $this->hasMany(Kepengurusan::class);
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
