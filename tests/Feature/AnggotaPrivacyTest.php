<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnggotaPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_photo_requires_consent_and_soft_deleted_member_is_hidden(): void
    {
        $this->seed();
        Storage::fake('r2_private');
        $anggota = Anggota::first();
        $anggota->update(['foto' => 'anggota/photo.webp']);
        Storage::disk('r2_private')->put($anggota->foto, 'photo');
        $url = route('media.anggota', $anggota->id);
        $this->get($url)->assertNotFound();
        $this->assertNull($anggota->foto_url);
        $anggota->update(['setuju_publikasi' => true]);
        $this->get($url)->assertOk();
        $anggota->update(['setuju_publikasi' => false]);
        $this->get($url)->assertNotFound();
        $this->actingAs(User::first())->get($url)->assertOk();
        $anggota->delete();
        $this->get($url)->assertNotFound();
    }

    public function test_related_member_is_protected_from_deletion(): void
    {
        $this->seed();
        $anggota = Anggota::first();
        $this->actingAs(User::first())->delete(route('admin.anggota.destroy', $anggota))
            ->assertSessionHasErrors('anggota');
        $this->assertNull($anggota->fresh()->deleted_at);
    }

    public function test_activity_page_is_accessible_to_admin_and_private_to_guests(): void
    {
        $this->get('/admin/riwayat')->assertRedirect('/login');
        $this->seed();
        $this->actingAs(User::first())->get('/admin/riwayat')->assertOk()->assertSee('Riwayat Aktivitas');
    }

    public function test_admin_biro_cannot_view_unapproved_photo_or_activity_history(): void
    {
        $this->seed();
        Storage::fake('r2_private');
        $anggota = Anggota::first();
        $anggota->update(['foto' => 'anggota/private.webp']);
        Storage::disk('r2_private')->put($anggota->foto, 'photo');
        $user = User::first();
        $user->update(['role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $this->actingAs($user)->get(route('media.anggota', $anggota->id))->assertNotFound();
        $this->assertNull($anggota->foto_url);
        $this->get('/admin/riwayat')->assertForbidden();
    }

    public function test_member_consent_changes_are_recorded_without_contact_details(): void
    {
        $this->seed();
        $anggota = Anggota::first();
        $data = $anggota->only(['nim', 'nama_lengkap', 'angkatan', 'status']);
        $this->actingAs(User::first())->put(route('admin.anggota.update', $anggota), $data + ['setuju_publikasi' => 1])->assertSessionHasNoErrors();
        $this->assertNotNull($anggota->fresh()->setuju_publikasi_at);
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_tipe' => 'anggota', 'subjek_id' => $anggota->id, 'aksi' => 'perubahan', 'ringkasan' => 'Data anggota diperbarui; izin publikasi aktif.']);
        $this->put(route('admin.anggota.update', $anggota), $data)->assertSessionHasNoErrors();
        $this->assertFalse($anggota->fresh()->setuju_publikasi);
    }
}
