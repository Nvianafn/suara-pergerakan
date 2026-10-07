<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Pembina;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembinaTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_profile_can_be_created_and_reused_across_periods(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $periods = Periode::all();
        $this->post(route('admin.pembina.store', $periods[0]), [
            'nama_lengkap' => 'Pembina Eksternal', 'urutan' => 0,
        ])->assertSessionHasNoErrors();
        $profil = Pembina::firstOrFail();
        $this->assertNull($profil->anggota_id);
        $this->post(route('admin.pembina.store', $periods[1]), [
            'pembina_id' => $profil->id, 'urutan' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame(2, $profil->periode()->count());
        $this->post(route('admin.pembina.store', $periods[1]), [
            'pembina_id' => $profil->id, 'urutan' => 1,
        ])->assertSessionHasErrors('pembina_id');
        $this->delete(route('admin.pembina.destroy', [$periods[0], $profil]));
        $this->assertSame(1, $profil->periode()->count());
        $this->assertDatabaseCount('pembina', 1);
    }

    public function test_public_structure_shows_pembina_before_bph_and_hides_unapproved_description(): void
    {
        $this->seed();
        $profil = Pembina::create(['nama_lengkap' => 'Pembina Eksternal', 'keterangan' => 'Keterangan privat']);
        Periode::aktif()->firstOrFail()->pembina()->attach($profil->id, ['urutan' => 0]);
        $this->get('/kepengurusan')->assertOk()->assertSeeInOrder(['Pembina Eksternal', 'Badan Pengurus Harian'])->assertDontSee('Keterangan privat');
        $profil->update(['setuju_publikasi' => true]);
        $this->assertNotNull($profil->fresh()->setuju_publikasi_at);
        $this->get('/kepengurusan')->assertSee('Keterangan privat');
    }

    public function test_admin_biro_cannot_manage_pembina(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $this->actingAs($user)->post(route('admin.pembina.store', Periode::first()), [
            'nama_lengkap' => 'Tidak Diizinkan', 'urutan' => 0,
        ])->assertForbidden();
        $this->assertDatabaseCount('pembina', 0);
    }
}
