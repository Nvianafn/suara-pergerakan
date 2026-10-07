<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BiroContentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_biro_cannot_edit_other_biro_activity_or_delete_content(): void
    {
        $this->seed();
        $user = User::create(['name' => 'Biro', 'email' => 'biro@test.id', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $kegiatan = Kegiatan::create(['judul' => 'Rayon', 'tanggal' => '2026-10-07', 'status' => 'draft', 'created_by' => User::first()->id]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
        $anggota = Anggota::first();
        $this->get(route('admin.anggota.index'))->assertOk()->assertDontSee($anggota->nim);
        $this->get(route('admin.kegiatan.edit', $kegiatan))->assertForbidden();
        $this->get(route('admin.kegiatan.preview', $kegiatan))->assertForbidden();
        $this->delete(route('admin.kegiatan.destroy', $kegiatan))->assertForbidden();
        $this->assertDatabaseHas('riwayat_aktivitas', ['user_id' => $user->id, 'aksi' => 'penolakan']);
    }

    public function test_biro_submission_is_draft_and_scope_is_locked_on_create_and_update(): void
    {
        $this->seed();
        $user = User::create(['name' => 'Biro', 'email' => 'biro@test.id', 'password' => 'password', 'role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $data = ['judul' => 'Karya Baru', 'konten' => 'Isi', 'tipe' => 'esai', 'status' => 'published', 'biro_id' => Biro::orderByDesc('id')->first()->id, 'periode_id' => Periode::where('is_aktif', false)->first()->id];
        $this->actingAs($user)->post(route('admin.karya.store'), $data)->assertForbidden();
        $data['status'] = 'draft';
        $this->post(route('admin.karya.store'), $data)->assertSessionHasNoErrors();
        $karya = Karya::where('judul', 'Karya Baru')->firstOrFail();
        $this->assertSame('draft', $karya->status);
        $this->assertSame($user->biro_id, $karya->biro_id);
        $this->assertSame((int) Periode::aktif()->value('id'), $karya->periode_id);
        $original = $karya->periode_id;
        $this->get(route('admin.karya.preview', $karya))->assertOk();
        Periode::where('is_aktif', true)->update(['is_aktif' => false]);
        $this->put(route('admin.karya.update', $karya), $data)->assertSessionHasNoErrors();
        $this->assertSame($original, $karya->fresh()->periode_id);
        $karya->update(['status' => 'published']);
        $this->put(route('admin.karya.update', $karya), $data)->assertForbidden();
        $this->get(route('admin.karya.preview', $karya))->assertForbidden();
    }

    public function test_soft_deleted_content_is_hidden_and_media_is_retained(): void
    {
        $this->seed();
        Storage::fake('public');
        $karya = Karya::first();
        $karya->update(['thumbnail' => 'karya/photo.webp']);
        Storage::disk('public')->put('karya/photo.webp', 'photo');
        $url = route('karya.show', $karya);
        $this->actingAs(User::first())->delete(route('admin.karya.destroy', $karya))->assertRedirect();
        $this->assertSoftDeleted('karya', ['id' => $karya->id]);
        Storage::disk('public')->assertExists('karya/photo.webp');
        $this->get($url)->assertNotFound();
    }
}
