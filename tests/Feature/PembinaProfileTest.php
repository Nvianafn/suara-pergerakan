<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Pembina;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembinaProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_can_be_managed_before_first_period_without_creating_member_or_account(): void
    {
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'super_admin']);
        $this->actingAs($user)->get(route('admin.kepengurusan.index'))->assertOk()->assertSee('Tambah Pembina');
        $this->get(route('admin.pembina.create'))->assertOk()->assertSee('Tidak perlu terdaftar sebagai anggota');
        $this->post(route('admin.pembina.create-profile'), ['nama_lengkap' => 'Pembina Eksternal'])->assertSessionHasNoErrors();
        $profil = Pembina::firstOrFail();
        $this->assertDatabaseCount('periode', 0);
        $this->assertDatabaseCount('anggota', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertFalse($profil->setuju_publikasi);
        $this->put(route('admin.pembina.update-profile', $profil), ['nama_lengkap' => 'Nama Baru'])->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', $profil->fresh()->nama_lengkap);
        $this->assertSame(0, $profil->periode()->count());
    }

    public function test_member_link_is_editable_unique_and_does_not_copy_member_consent_or_photo(): void
    {
        $this->seed();
        $anggota = Anggota::first();
        $anggota->update(['setuju_publikasi' => true, 'foto' => 'anggota/private.webp']);
        $profil = Pembina::create(['nama_lengkap' => 'Pembina Mandiri']);
        $other = Pembina::create(['nama_lengkap' => 'Profil lain']);
        $this->actingAs(User::first())->put(route('admin.pembina.update-profile', $profil), [
            'nama_lengkap' => $profil->nama_lengkap, 'anggota_id' => $anggota->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($anggota->id, $profil->fresh()->anggota_id);
        $this->assertFalse($profil->fresh()->setuju_publikasi);
        $this->assertNull($profil->fresh()->foto);
        $this->put(route('admin.pembina.update-profile', $other), [
            'nama_lengkap' => $other->nama_lengkap, 'anggota_id' => $anggota->id,
        ])->assertSessionHasErrors('anggota_id');
        $this->put(route('admin.pembina.update-profile', $profil), [
            'nama_lengkap' => $profil->nama_lengkap, 'anggota_id' => null,
        ])->assertSessionHasNoErrors();
        $this->assertNull($profil->fresh()->anggota_id);
    }

    public function test_period_management_url_selects_requested_period_and_profile_edits_keep_placements(): void
    {
        $this->seed();
        $periods = Periode::all();
        $profil = Pembina::create(['nama_lengkap' => 'Lintas Periode']);
        foreach ($periods as $periode) {
            $periode->pembina()->attach($profil->id, ['urutan' => 3]);
        }
        $this->actingAs(User::first())->get(route('admin.periode.pengurus', $periods[1]))->assertOk()->assertViewHas('selectedId', $periods[1]->id);
        $this->put(route('admin.pembina.update-profile', $profil), ['nama_lengkap' => 'Nama Terkini'])->assertSessionHasNoErrors();
        $this->assertSame($periods->count(), $profil->periode()->count());
        $this->assertSame(3, (int) $profil->periode()->first()->pivot->urutan);
        $this->delete(route('admin.periode.destroy', $periods[1]))->assertSessionHasErrors(['periode' => 'Periode ini tidak dapat dihapus'.($periods[1]->is_aktif ? ' karena masih aktif' : '').': 1 pembina, '.$periods[1]->kepengurusan()->count().' pengurus, 0 kegiatan, 0 karya.']);
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_tipe' => 'pembina', 'subjek_id' => $profil->id, 'aksi' => 'perubahan']);
    }

    public function test_biro_admin_cannot_manage_standalone_profiles(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['role' => 'admin_biro', 'biro_id' => Biro::first()->id]);
        $profil = Pembina::create(['nama_lengkap' => 'Pembina']);
        $this->actingAs($user)->get(route('admin.pembina.create'))->assertForbidden();
        $this->get(route('admin.pembina.edit', $profil))->assertForbidden();
        $this->actingAs($user)->post(route('admin.pembina.create-profile'), ['nama_lengkap' => 'Dilarang'])->assertForbidden();
        $this->put(route('admin.pembina.update-profile', $profil), ['nama_lengkap' => 'Dilarang'])->assertForbidden();
        $this->assertSame('Pembina', $profil->fresh()->nama_lengkap);
    }

    public function test_new_external_profile_can_be_placed_in_one_submission(): void
    {
        $this->seed();
        $periode = Periode::first();
        $members = Anggota::count();
        $users = User::count();
        $this->actingAs(User::first())->post(route('admin.pembina.create-profile'), [
            'nama_lengkap' => 'Pembina Dari Luar', 'periode_id' => $periode->id, 'urutan' => 2,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.kepengurusan.index', ['periode' => $periode->id]));
        $profil = Pembina::where('nama_lengkap', 'Pembina Dari Luar')->firstOrFail();
        $this->assertNull($profil->anggota_id);
        $this->assertSame($members, Anggota::count());
        $this->assertSame($users, User::count());
        $this->assertDatabaseHas('periode_pembina', ['periode_id' => $periode->id, 'pembina_id' => $profil->id, 'urutan' => 2]);
        $this->get(route('admin.pembina.edit', ['pembina' => $profil, 'periode' => $periode->id]))->assertOk()->assertViewHas('urutan', 2);
        $this->put(route('admin.pembina.update', [$periode, $profil]), [
            'nama_lengkap' => 'Nama Setelah Edit', 'urutan' => 4,
        ])->assertRedirect(route('admin.kepengurusan.index', ['periode' => $periode->id]));
        $this->assertSame('Nama Setelah Edit', $profil->fresh()->nama_lengkap);
        $this->assertDatabaseHas('periode_pembina', ['periode_id' => $periode->id, 'pembina_id' => $profil->id, 'urutan' => 4]);
        $other = Periode::where('id', '!=', $periode->id)->firstOrFail();
        $this->get(route('admin.pembina.edit', ['pembina' => $profil, 'periode' => $other->id]))->assertNotFound();
    }
}
