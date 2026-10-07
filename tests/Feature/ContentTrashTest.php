<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\User;
use App\Services\MediaCleanup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_restore_as_draft_but_cannot_purge(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['role' => 'admin']);
        $karya = Karya::first();
        $karya->update(['status' => 'published', 'published_at' => now()]);
        $publishedAt = $karya->published_at;
        $karya->delete();
        $this->actingAs($user)->get(route('admin.trash.index'))->assertOk()->assertSee($karya->judul);
        $this->delete(route('admin.trash.purge', ['karya', $karya->id]))->assertForbidden();
        $this->post(route('admin.trash.restore', ['karya', $karya->id]))->assertRedirect();
        $restored = Karya::findOrFail($karya->id);
        $this->assertSame('draft', $restored->status);
        $this->assertTrue($restored->published_at->equalTo($publishedAt));
        $this->get(route('karya.show', $restored))->assertNotFound();
    }

    public function test_super_admin_purge_removes_gallery_and_thumbnail_and_records_audit(): void
    {
        $this->seed();
        Storage::fake('public');
        $kegiatan = Kegiatan::first();
        $kegiatan->update(['thumbnail' => 'kegiatan/thumb.webp']);
        $foto = $kegiatan->foto()->create(['path' => 'kegiatan/gallery.webp', 'urutan' => 1]);
        Storage::disk('public')->put($kegiatan->thumbnail, 'thumb');
        Storage::disk('public')->put($foto->path, 'gallery');
        $kegiatan->delete();
        $this->actingAs(User::first())->delete(route('admin.trash.purge', ['kegiatan', $kegiatan->id]))->assertRedirect();
        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
        $this->assertDatabaseMissing('kegiatan_foto', ['id' => $foto->id]);
        Storage::disk('public')->assertMissing('kegiatan/thumb.webp');
        Storage::disk('public')->assertMissing('kegiatan/gallery.webp');
        $this->assertDatabaseHas('riwayat_aktivitas', ['aksi' => 'penghapusan_permanen', 'subjek_id' => $kegiatan->id]);
        $this->assertDatabaseCount('media_cleanup', 0);
    }

    public function test_failed_storage_cleanup_is_retained_for_retry(): void
    {
        MediaCleanup::enqueue('public', 'retry.webp');
        $disk = \Mockery::mock();
        $disk->shouldReceive('delete')->once()->with('retry.webp')->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);
        $this->assertSame(1, MediaCleanup::run());
        $this->assertDatabaseHas('media_cleanup', ['path' => 'retry.webp']);
    }

    public function test_biro_admin_cannot_restore_and_used_biro_cannot_be_deleted(): void
    {
        $this->seed();
        $biro = Biro::where('tipe', 'biro')->firstOrFail();
        $user = User::create(['name' => 'Biro', 'email' => 'biro@example.test', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => $biro->id]);
        $karya = Karya::first();
        $karya->delete();
        $this->actingAs($user)->post(route('admin.trash.restore', ['karya', $karya->id]))->assertForbidden();
        $this->actingAs(User::first())->delete(route('admin.biro.destroy', $biro))->assertRedirect();
        $this->assertDatabaseHas('biro', ['id' => $biro->id]);
    }
}
