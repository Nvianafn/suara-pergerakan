<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_are_sanitized_rendered_and_audited(): void
    {
        $this->seed();
        $this->actingAs(User::first())->put(route('admin.settings.update'), [
            'nama_rayon' => 'Rayon Uji', 'tagline' => 'Tagline baru',
            'tentang_deskripsi' => '<p>Deskripsi baru</p><script>alert(1)</script>',
            'tentang_sejarah' => '<h2>Sejarah baru</h2>', 'visi' => '<b>Visi teks</b>',
            'misi' => '<ul><li>Misi baru</li></ul>',
            'peta_url' => 'https://maps.app.goo.gl/example', 'sosmed_tiktok' => 'https://www.tiktok.com/@rayon',
        ])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('<script', Setting::get('tentang_deskripsi'));
        $this->get(route('tentang'))->assertOk()->assertSee('Deskripsi baru')->assertSee('Sejarah baru')->assertSee('Misi baru')->assertSee('&lt;b&gt;Visi teks&lt;/b&gt;', false)->assertDontSee('<script>alert(1)', false);
        $this->get(route('kontak'))->assertOk()->assertSee('https://maps.app.goo.gl/example', false)->assertSee('TikTok')->assertDontSee('<iframe', false);
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_tipe' => 'settings', 'aksi' => 'perubahan']);
    }

    public function test_unsafe_urls_and_biro_admin_changes_are_rejected(): void
    {
        $this->seed();
        $this->actingAs(User::first())->put(route('admin.settings.update'), ['nama_rayon' => 'Uji', 'sosmed_instagram' => 'javascript:alert(1)', 'peta_url' => 'https://google.com.evil.test/maps'])->assertSessionHasErrors(['sosmed_instagram', 'peta_url']);
        $user = User::create(['name' => 'Biro', 'email' => 'settings-biro@example.test', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $this->actingAs($user)->put(route('admin.settings.update'), ['nama_rayon' => 'Ditolak'])->assertForbidden();
        $this->assertNotSame('Ditolak', Setting::get('nama_rayon'));
    }

    public function test_legacy_social_keys_are_retained_until_canonical_value_is_saved(): void
    {
        Setting::put('instagram', 'https://instagram.com/legacy');
        $this->assertSame('https://instagram.com/legacy', Setting::get('sosmed_instagram'));
        Setting::put('sosmed_instagram', '');
        $this->assertSame('', Setting::get('instagram'));
    }

    public function test_replacing_site_image_updates_public_render_and_cleans_old_file(): void
    {
        $this->seed();
        Storage::fake('public');
        Storage::disk('public')->put('settings/old.webp', 'old');
        Setting::put('hero_image', 'settings/old.webp');
        $this->mock(ImageService::class)->shouldReceive('store')->once()->andReturn('settings/new.webp');
        $file = UploadedFile::fake()->createWithContent('hero.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a3ioAAAAASUVORK5CYII='));
        $this->actingAs(User::first())->put(route('admin.settings.update'), ['nama_rayon' => 'Rayon Uji', 'hero_image' => $file])->assertSessionHasNoErrors();
        $this->assertSame('settings/new.webp', Setting::get('hero_image'));
        Storage::disk('public')->assertMissing('settings/old.webp');
        $this->get('/')->assertOk()->assertSee('settings/new.webp', false);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Tampilan &amp; SEO', false);
        $this->actingAs(User::first())->put(route('admin.settings.update'), ['nama_rayon' => 'Rayon Uji'])->assertSessionHasNoErrors();
        $this->assertSame('settings/new.webp', Setting::get('hero_image'));
    }
}
