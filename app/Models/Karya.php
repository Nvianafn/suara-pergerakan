<?php

namespace App\Models;

use App\Models\Concerns\HasBiroSnapshot;
use App\Models\Concerns\HasSafePublishedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Karya extends Model
{
    use HasBiroSnapshot;
    use HasSafePublishedContent;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'karya';

    protected $fillable = [
        'anggota_id', 'judul', 'slug', 'tipe', 'konten', 'excerpt',
        'thumbnail', 'tags', 'status', 'published_at', 'created_by',
        'biro_id', 'periode_id', 'biro_nama',
        'penulis_tipe', 'penulis_nama',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'biro_id' => 'integer',
        'periode_id' => 'integer',
        'created_by' => 'integer',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function biro(): BelongsTo
    {
        return $this->belongsTo(Biro::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('judul')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penulis(): string
    {
        return match ($this->penulis_tipe) {
            'anggota' => $this->anggota?->nama_lengkap ?? 'Penulis',
            'nama_bebas' => $this->penulis_nama,
            'anonim' => 'Anonim',
            default => 'Redaksi '.Setting::get('nama_rayon', 'PMII Rayon Saintek'),
        };
    }

    protected static function booted(): void
    {
        static::saving(function (self $karya) {
            if (! $karya->penulis_tipe) {
                $karya->penulis_tipe = $karya->anggota_id ? 'anggota' : 'redaksi';
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
