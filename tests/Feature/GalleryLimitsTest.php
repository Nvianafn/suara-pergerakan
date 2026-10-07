<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GalleryLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function image(int $kilobytes = 1): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('foto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a3ioAAAAASUVORK5CYII='))->size($kilobytes);
    }

    private function payload(): array
    {
        return ['judul' => 'Uji galeri', 'tanggal' => '2026-10-07', 'status' => 'draft'];
    }

    public function test_server_rejects_count_file_size_total_size_and_disguised_file(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $this->mock(ImageService::class)->shouldNotReceive('store');
        $url = route('admin.kegiatan.store');
        $this->post($url, $this->payload() + ['foto' => array_map(fn () => $this->image(), range(1, 21))])->assertSessionHasErrors('foto');
        $this->post($url, $this->payload() + ['foto' => [$this->image(5121)]])->assertSessionHasErrors('foto.0');
        $this->post($url, $this->payload() + ['foto' => array_map(fn () => $this->image(5120), range(1, 8)), 'thumbnail' => $this->image()])->assertSessionHasErrors('foto');
        $path = tempnam(sys_get_temp_dir(), 'invalid-image-');
        try {
            file_put_contents($path, '<?php echo "not an image";');
            $file = new UploadedFile($path, 'fake.png', 'image/png', null, true);
            $this->post($url, $this->payload() + ['foto' => [$file]])->assertSessionHasErrors('foto.0');
        } finally {
            unlink($path);
        }
        $this->assertDatabaseMissing('kegiatan', ['judul' => 'Uji galeri']);
    }

    public function test_existing_photos_count_and_foreign_removal_does_not_free_space(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $work = Kegiatan::create($this->payload());
        for ($i = 0; $i < 20; $i++) {
            $work->foto()->create(['path' => 'galeri/'.$i.'.webp', 'urutan' => $i]);
        }
        $other = Kegiatan::create($this->payload() + ['lokasi' => 'Lain']);
        $foreign = $other->foto()->create(['path' => 'galeri/other.webp']);
        $this->mock(ImageService::class)->shouldNotReceive('store');
        $this->put(route('admin.kegiatan.update', $work), $this->payload() + ['foto' => [$this->image()], 'hapus_foto' => [$foreign->id]])->assertSessionHasErrors('foto');
        $this->assertSame(20, $work->foto()->count());
        $this->assertSame(1, $other->foto()->count());
    }

    public function test_five_mb_file_can_replace_own_photo_at_capacity(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $work = Kegiatan::create($this->payload());
        for ($i = 0; $i < 20; $i++) {
            $photo = $work->foto()->create(['path' => 'galeri/'.$i.'.webp', 'urutan' => $i]);
        }
        $this->mock(ImageService::class, function ($mock) use ($photo) {
            $mock->shouldReceive('store')->once()->andReturn('galeri/new.webp');
            $mock->shouldReceive('delete')->once()->with($photo->path);
        });
        $this->put(route('admin.kegiatan.update', $work), $this->payload() + ['foto' => [$this->image(5120)], 'caption' => ['Dokumentasi diskusi'], 'hapus_foto' => [$photo->id]])->assertSessionHasNoErrors();
        $this->assertSame(20, $work->foto()->count());
        $this->assertDatabaseHas('kegiatan_foto', ['kegiatan_id' => $work->id, 'path' => 'galeri/new.webp', 'caption' => 'Dokumentasi diskusi']);
    }
}
