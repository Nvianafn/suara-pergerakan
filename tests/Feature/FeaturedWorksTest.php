<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Karya;
use App\Models\User;
use App\Services\FeaturedWorks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_prioritizes_selection_and_fills_to_four_without_duplicates(): void
    {
        $this->seed();
        for ($i = 0; $i < 7; $i++) {
            Karya::create(['judul' => 'Pilihan '.$i, 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'published', 'is_featured' => $i === 0]);
        }
        Karya::create(['judul' => 'Draft tersembunyi', 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'draft', 'is_featured' => true]);
        $items = app(FeaturedWorks::class)->forHome();
        $this->assertCount(4, $items);
        $this->assertSame('Pilihan 0', $items->first()->judul);
        $this->assertCount(4, $items->unique('id'));
        $this->assertFalse($items->contains('judul', 'Draft tersembunyi'));
        $this->get('/')->assertOk()->assertSee('Pilihan 0')->assertDontSee('Draft tersembunyi');
    }

    public function test_cms_rejects_seventh_selection_and_featured_drafts(): void
    {
        $this->seed();
        for ($i = 0; $i < 6; $i++) {
            Karya::create(['judul' => 'Pilihan '.$i, 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'published', 'is_featured' => true]);
        }
        $this->actingAs(User::first());
        $data = ['judul' => 'Ketujuh', 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'published', 'is_featured' => 1];
        $this->post(route('admin.karya.store'), $data)->assertSessionHasErrors('is_featured');
        $data['status'] = 'draft';
        $this->post(route('admin.karya.store'), $data)->assertSessionHasErrors('is_featured');
        $this->assertDatabaseMissing('karya', ['judul' => 'Ketujuh']);
    }

    public function test_biro_admin_cannot_select_featured_work(): void
    {
        $this->seed();
        $user = User::create(['name' => 'Biro', 'email' => 'biro-featured@example.test', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $this->actingAs($user)->post(route('admin.karya.store'), ['judul' => 'Ditolak', 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'draft', 'is_featured' => 1])->assertForbidden();
    }

    public function test_existing_selection_can_be_edited_at_limit_and_unselected(): void
    {
        $this->seed();
        for ($i = 0; $i < 6; $i++) {
            $work = Karya::create(['judul' => 'Pilihan '.$i, 'tipe' => 'esai', 'konten' => 'Isi', 'status' => 'published', 'is_featured' => true]);
        }
        $this->actingAs(User::first());
        $data = ['judul' => $work->judul, 'tipe' => 'esai', 'konten' => 'Isi diperbarui', 'status' => 'published', 'is_featured' => 1];
        $this->put(route('admin.karya.update', $work), $data)->assertSessionHasNoErrors();
        $this->assertTrue($work->fresh()->is_featured);
        unset($data['is_featured']);
        $data['status'] = 'draft';
        $this->put(route('admin.karya.update', $work), $data)->assertSessionHasNoErrors();
        $this->assertFalse($work->fresh()->is_featured);
        $this->assertFalse(app(FeaturedWorks::class)->forHome()->contains('id', $work->id));
    }
}
