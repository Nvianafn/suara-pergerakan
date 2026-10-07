<?php

namespace Tests\Feature;

use App\Models\Pembina;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembinaPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_requires_consent_and_placement_and_revocation_blocks_access(): void
    {
        $this->seed();
        Storage::fake('r2_private');
        Storage::disk('r2_private')->put('pembina/test.webp', 'private-photo');
        $profil = Pembina::create(['nama_lengkap' => 'Pembina']);
        $profil->foto = 'pembina/test.webp';
        $profil->save();
        $url = route('media.pembina', $profil);
        $this->get($url)->assertNotFound()->assertHeader('Cache-Control', 'no-store, private');
        $profil->update(['setuju_publikasi' => true]);
        $this->get($url)->assertNotFound();
        Periode::first()->pembina()->attach($profil->id);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/webp');
        $profil->update(['setuju_publikasi' => false]);
        $this->get($url)->assertNotFound();
        $this->actingAs(User::first())->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_profile_changes_are_logged_and_invalid_upload_is_rejected(): void
    {
        $this->seed();
        $periode = Periode::first();
        $profil = Pembina::create(['nama_lengkap' => 'Pembina']);
        $periode->pembina()->attach($profil->id);
        $this->actingAs(User::first())->put(route('admin.pembina.update', [$periode, $profil]), [
            'nama_lengkap' => 'Nama Baru', 'urutan' => 2, 'setuju_publikasi' => true,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_id' => $profil->id, 'aksi' => 'perubahan']);
        $this->put(route('admin.pembina.update', [$periode, $profil]), [
            'nama_lengkap' => 'Nama Baru', 'urutan' => 2,
            'foto' => UploadedFile::fake()->create('fake.jpg', 1, 'text/plain'),
        ])->assertSessionHasErrors('foto');
    }

    public function test_used_profile_is_protected_and_unused_profile_can_be_deleted(): void
    {
        $this->seed();
        $profil = Pembina::create(['nama_lengkap' => 'Pembina']);
        $periode = Periode::first();
        $periode->pembina()->attach($profil->id);
        $this->actingAs(User::first())->delete(route('admin.pembina.delete-profile', $profil))->assertSessionHasErrors('pembina');
        $this->assertDatabaseHas('pembina', ['id' => $profil->id]);
        $periode->pembina()->detach($profil->id);
        $this->delete(route('admin.pembina.delete-profile', $profil));
        $this->assertDatabaseMissing('pembina', ['id' => $profil->id]);
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_id' => $profil->id, 'aksi' => 'penghapusan']);
    }
}
