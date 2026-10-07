<?php

namespace Tests\Feature;

use App\Services\MediaCleanup;
use App\Services\PrivateImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateMediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_private_delete_is_retained_and_retried(): void
    {
        Storage::shouldReceive('disk')->with('r2_private')->once()->andReturnSelf();
        Storage::shouldReceive('delete')->with('pembina/example.webp')->once()->andReturn(false);
        app(PrivateImageService::class)->delete('pembina/example.webp');
        $this->assertDatabaseHas('media_cleanup', ['disk' => 'r2_private', 'path' => 'pembina/example.webp']);

        Storage::shouldReceive('disk')->with('r2_private')->once()->andReturnSelf();
        Storage::shouldReceive('delete')->with('pembina/example.webp')->once()->andReturn(true);
        $this->assertSame(0, MediaCleanup::run());
        $this->assertDatabaseCount('media_cleanup', 0);
    }
}
