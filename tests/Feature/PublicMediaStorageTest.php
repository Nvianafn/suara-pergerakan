<?php

namespace Tests\Feature;

use App\Services\PublicMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_copy_preserves_source_and_rejects_conflicting_destination(): void
    {
        Storage::fake('public');
        Storage::fake('r2_public');
        Storage::disk('public')->put('kegiatan/example.webp', 'original');
        $this->artisan('media:copy-public-to-r2')->assertSuccessful();
        $this->assertSame('original', Storage::disk('r2_public')->get('kegiatan/example.webp'));
        Storage::disk('public')->assertExists('kegiatan/example.webp');
        $this->artisan('media:copy-public-to-r2')->assertSuccessful();
        Storage::disk('r2_public')->put('kegiatan/example.webp', 'different');
        $this->artisan('media:copy-public-to-r2')->assertFailed();
        $this->assertSame('different', Storage::disk('r2_public')->get('kegiatan/example.webp'));
    }

    public function test_public_media_uses_selected_disk_url(): void
    {
        config(['filesystems.public_media_disk' => 'r2_public', 'filesystems.disks.r2_public.url' => 'https://assets.example.test']);
        $this->assertSame('r2_public', PublicMedia::disk());
        $this->assertSame('https://assets.example.test/kegiatan/example.webp', PublicMedia::url('kegiatan/example.webp'));
    }

    public function test_copy_refuses_legacy_private_photos(): void
    {
        Storage::fake('public');
        Storage::fake('r2_public');
        Storage::disk('public')->put('anggota/photo.webp', 'private');
        $this->artisan('media:copy-public-to-r2')->assertFailed();
        Storage::disk('r2_public')->assertMissing('anggota/photo.webp');
    }
}
