<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Kegiatan;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BiroDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_biro_can_only_update_its_own_description(): void
    {
        $this->seed();
        $biro = Biro::first();
        $user = User::create(['name' => 'Biro', 'email' => 'biro@example.test', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => $biro->id]);
        $name = $biro->nama;
        $this->actingAs($user)->get(route('admin.biro.description.edit', $biro))->assertOk();
        $this->put(route('admin.biro.description.update', $biro), ['deskripsi' => 'Deskripsi baru', 'nama' => 'Nama palsu', 'urutan' => 255])->assertSessionHasNoErrors();
        $this->assertSame($name, $biro->fresh()->nama);
        $this->assertSame('Deskripsi baru', $biro->fresh()->deskripsi);
        $other = Biro::where('id', '!=', $biro->id)->firstOrFail();
        $this->put(route('admin.biro.description.update', $other), ['deskripsi' => 'Tidak boleh'])->assertForbidden();
        $this->assertDatabaseHas('riwayat_aktivitas', ['user_id' => $user->id, 'aksi' => 'penolakan']);
    }

    public function test_admin_cannot_permanently_delete_gallery_photo_through_update(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['role' => 'admin']);
        $kegiatan = Kegiatan::first();
        $foto = $kegiatan->foto()->create(['path' => 'kegiatan/photo.webp', 'urutan' => 1]);
        $this->actingAs($user)->put(route('admin.kegiatan.update', $kegiatan), [
            'judul' => 'Ganti', 'tanggal' => '2026-10-07', 'status' => 'draft', 'hapus_foto' => [$foto->id],
        ])->assertForbidden();
        $this->assertDatabaseHas('kegiatan_foto', ['id' => $foto->id]);
    }

    public function test_failed_gallery_upload_rolls_back_activity_and_cleans_new_thumbnail(): void
    {
        $this->seed();
        $before = Kegiatan::count();
        $image = \Mockery::mock(ImageService::class);
        $image->shouldReceive('store')->once()->withAnyArgs()->andReturn('kegiatan/new.webp');
        $image->shouldReceive('store')->once()->withAnyArgs()->andThrow(new \RuntimeException('Storage unavailable'));
        $image->shouldReceive('delete')->once()->with('kegiatan/new.webp');
        $this->app->instance(ImageService::class, $image);
        $file = UploadedFile::fake()->createWithContent('image.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQkAAAAASUVORK5CYII='));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs(User::first())->post(route('admin.kegiatan.store'), [
                'judul' => 'Upload gagal', 'tanggal' => '2026-10-07', 'status' => 'draft', 'thumbnail' => $file, 'foto' => [$file],
            ]);
            $this->fail('Expected storage failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Storage unavailable', $exception->getMessage());
        }
        $this->assertSame($before, Kegiatan::count());
        $this->assertDatabaseMissing('riwayat_aktivitas', ['subjek_tipe' => 'kegiatan', 'aksi' => 'pembuatan']);
    }
}
