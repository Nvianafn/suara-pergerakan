<?php

namespace Tests\Feature;

use App\Services\ImageService;
use App\Services\PrivateImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{
    public function test_public_jpeg_is_reencoded_and_resized_without_original_payload(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('Requires GD runtime.');
        }
        Storage::fake('public');
        $file = tempnam(sys_get_temp_dir(), 'public-image-');
        try {
            $image = imagecreatetruecolor(1800, 900);
            imagejpeg($image, $file);
            file_put_contents($file, 'HIDDEN_JPEG_PAYLOAD', FILE_APPEND);
            $service = app(ImageService::class);
            $path = $service->store(new UploadedFile($file, 'original.jpg', 'image/jpeg', null, true), 'kegiatan');
            $bytes = Storage::disk('public')->get($path);
            $size = getimagesizefromstring($bytes);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertSame(1600, $size[0]);
            $this->assertSame(800, $size[1]);
            $this->assertStringNotContainsString('HIDDEN_JPEG_PAYLOAD', $bytes);
            $service->delete($path);
            Storage::disk('public')->assertMissing($path);
        } finally {
            unlink($file);
        }
    }

    public function test_private_image_is_reencoded_resized_and_payload_removed(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('Requires GD runtime.');
        }
        Storage::fake('r2_private');
        $file = tempnam(sys_get_temp_dir(), 'real-image-');
        try {
            $image = imagecreatetruecolor(1600, 800);
            imagepng($image, $file);
            file_put_contents($file, '<?php echo "HIDDEN_PAYLOAD"; ?>', FILE_APPEND);
            $path = app(PrivateImageService::class)->store(new UploadedFile($file, '../unsafe.png', 'image/png', null, true), 'pembina');
            $bytes = Storage::disk('r2_private')->get($path);
            $size = getimagesizefromstring($bytes);
            $this->assertMatchesRegularExpression('#^pembina/[A-Za-z0-9]{32}\.webp$#', $path);
            $this->assertSame('image/webp', $size['mime']);
            $this->assertSame(1200, $size[0]);
            $this->assertSame(600, $size[1]);
            $this->assertStringNotContainsString('HIDDEN_PAYLOAD', $bytes);
            app(PrivateImageService::class)->delete($path);
            Storage::disk('r2_private')->assertMissing($path);
        } finally {
            unlink($file);
        }
    }
}
