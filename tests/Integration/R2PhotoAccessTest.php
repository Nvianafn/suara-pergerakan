<?php

namespace Tests\Integration;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Pembina;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class R2PhotoAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_private_objects_are_streamed_only_with_permission(): void
    {
        if (getenv('RUN_R2_INTEGRATION') !== '1') {
            $this->markTestSkipped('Explicit RUN_R2_INTEGRATION=1 required for real storage.');
        }
        $this->assertSame('s3', config('filesystems.disks.r2_private.driver'));
        $this->seed();
        $disk = Storage::disk('r2_private');
        $path = 'connection-check/'.Str::uuid().'.webp';
        $image = imagecreatetruecolor(2, 2);
        ob_start();
        imagewebp($image);
        $bytes = ob_get_clean();
        try {
            $this->assertTrue($disk->put($path, $bytes));
            $member = Anggota::firstOrFail();
            $member->update(['foto' => $path, 'setuju_publikasi' => false]);
            $profile = Pembina::create(['nama_lengkap' => 'R2 integration']);
            $profile->foto = $path;
            $profile->save();
            $memberUrl = route('media.anggota', $member->id);
            $profileUrl = route('media.pembina', $profile);
            foreach ([$memberUrl, $profileUrl] as $url) {
                $this->get($url)->assertNotFound()->assertHeader('Cache-Control', 'no-store, private');
            }
            $member->update(['setuju_publikasi' => true]);
            $profile->update(['setuju_publikasi' => true]);
            $this->get($profileUrl)->assertNotFound();
            Periode::firstOrFail()->pembina()->attach($profile->id);
            foreach ([$memberUrl, $profileUrl] as $url) {
                $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/webp');
                $this->assertSame($bytes, $response->streamedContent());
                $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
            }
            $member->update(['setuju_publikasi' => false]);
            $profile->update(['setuju_publikasi' => false]);
            foreach ([$memberUrl, $profileUrl] as $url) {
                $this->get($url)->assertNotFound();
            }
            $user = User::firstOrFail();
            foreach ([$memberUrl, $profileUrl] as $url) {
                $response = $this->actingAs($user)->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
                $this->assertSame($bytes, $response->streamedContent());
            }
            $user->update(['role' => 'admin_biro', 'biro_id' => Biro::firstOrFail()->id]);
            foreach ([$memberUrl, $profileUrl] as $url) {
                $this->actingAs($user)->get($url)->assertNotFound();
            }
        } finally {
            $this->assertTrue($disk->delete($path));
            $this->assertFalse($disk->fileExists($path));
        }
    }
}
