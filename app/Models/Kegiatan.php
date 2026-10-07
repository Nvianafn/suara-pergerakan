<?php

namespace App\Models;

use App\Models\Concerns\HasBiroSnapshot;
use App\Models\Concerns\HasSafePublishedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Kegiatan extends Model
{
    use HasBiroSnapshot;
    use HasSafePublishedContent;
    use HasSlug;
    use SoftDeletes;

    protected $table = 'kegiatan';

    protected $fillable = [
        'biro_id', 'judul', 'slug', 'deskripsi', 'tanggal',
        'lokasi', 'thumbnail', 'status', 'created_by',
        'periode_id', 'biro_nama',
    ];

    protected $casts = [
        'biro_id' => 'integer',
        'periode_id' => 'integer',
        'created_by' => 'integer',
        'tanggal' => 'date',
        'published_at' => 'datetime',
    ];

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

    public function biro(): BelongsTo
    {
        return $this->belongsTo(Biro::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(KegiatanFoto::class)->orderBy('urutan');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
