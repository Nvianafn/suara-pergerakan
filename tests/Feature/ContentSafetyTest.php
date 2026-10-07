<?php

namespace Tests\Feature;

use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\User;
use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_allowlist_removes_active_content_and_untrusted_or_private_images(): void
    {
        config(['app.url' => 'https://suara.test', 'filesystems.disks.r2_public.url' => 'https://assets.test']);
        $html = '<p onclick="alert(1)">Aman<br><strong>Tebal</strong></p><script>alert(1)</script><iframe src="https://evil.test"></iframe><style>body{display:none}</style><form>Form</form>'
            .'<a href="javascript:alert(1)">Buruk</a><a href="https://external.test" title="Tautan">Baik</a>'
            .'<img src="https://evil.test/a.jpg" onerror="alert(1)"><img src="data:image/png;base64,AAAA"><img src="https://suara.test/media/pembina/1">'
            .'<img src="https://suara.test/storage/karya/a.webp" alt="Foto"><img src="https://assets.test/kegiatan/b.webp">';
        $clean = app(HtmlSanitizer::class)->clean($html);
        foreach (['onclick', 'onerror', '<script', '<iframe', '<style', '<form', 'javascript:', 'data:', 'evil.test', '/media/pembina/'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $clean);
        }
        $this->assertStringContainsString('<strong>Tebal</strong>', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $clean);
        $this->assertStringContainsString('https://suara.test/storage/karya/a.webp', $clean);
        $this->assertStringContainsString('https://assets.test/kegiatan/b.webp', $clean);
        $poem = app(HtmlSanitizer::class)->clean("Baris pertama\nBaris kedua");
        $this->assertStringContainsString('<br', $poem);
    }

    public function test_publication_time_is_server_generated_and_preserved_for_both_content_types(): void
    {
        $this->seed();
        foreach ([Karya::first(), Kegiatan::first()] as $content) {
            $content->status = 'draft';
            $content->save();
            $before = $content->published_at;
            $this->travel(1)->days();
            $content->status = 'published';
            $content->published_at = '2000-01-01 00:00:00';
            $content->save();
            $first = $content->fresh()->published_at;
            $this->assertNotSame('2000-01-01', $first->format('Y-m-d'));
            if ($before) {
                $this->assertTrue($first->equalTo($before));
            }
            $this->travel(1)->days();
            $content->status = 'draft';
            $content->save();
            $this->assertTrue($content->fresh()->published_at->equalTo($first));
            $content->status = 'published';
            $content->save();
            $this->assertTrue($content->fresh()->published_at->equalTo($first));
        }
    }

    public function test_legacy_html_is_safe_when_rendered_and_sanitization_preserves_metadata(): void
    {
        $this->seed();
        $karya = Karya::published()->firstOrFail();
        $timestamp = $karya->published_at;
        $unsafe = '<p>Konten aman</p><script>alert(99)</script><img src="https://evil.test/track.jpg" onerror="alert(99)">';
        DB::table('karya')->where('id', $karya->id)->update(['konten' => $unsafe]);
        $this->get(route('karya.show', $karya))->assertOk()->assertSee('Konten aman')->assertDontSee('alert(99)')->assertDontSee('evil.test');
        $this->artisan('content:sanitize')->assertSuccessful();
        $this->assertStringNotContainsString('<script', $karya->fresh()->konten);
        $this->assertTrue($karya->fresh()->published_at->equalTo($timestamp));
    }

    public function test_manual_publication_time_is_ignored_in_cms(): void
    {
        $this->seed();
        $this->actingAs(User::first())->post(route('admin.karya.store'), [
            'judul' => 'Publikasi baru', 'tipe' => 'esai', 'konten' => '<p onmouseover="alert(1)">Aman</p>',
            'status' => 'published', 'published_at' => '2000-01-01',
        ])->assertSessionHasNoErrors();
        $karya = Karya::where('judul', 'Publikasi baru')->firstOrFail();
        $this->assertSame(now()->format('Y-m-d'), $karya->published_at->format('Y-m-d'));
        $this->assertStringNotContainsString('onmouseover', $karya->konten);
    }

    public function test_unpublished_draft_has_no_publication_time_until_first_publish(): void
    {
        $this->seed();
        $draft = Kegiatan::create(['judul' => 'Draft belum terbit', 'tanggal' => '2026-10-07', 'status' => 'draft']);
        $this->assertNull($draft->published_at);
        $draft->status = 'published';
        $draft->save();
        $this->assertNotNull($draft->fresh()->published_at);
        $this->assertSame(now()->format('Y-m-d'), $draft->fresh()->published_at->format('Y-m-d'));
    }
}
