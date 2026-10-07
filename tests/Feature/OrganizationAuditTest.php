<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_biro_is_hidden_from_grid_but_remains_accessible_as_archive(): void
    {
        $this->seed();
        $biro = Biro::unitBiro()->firstOrFail();
        $kegiatan = Kegiatan::where('biro_id', $biro->id)->first();
        $snapshot = $kegiatan?->biro_nama;
        $this->actingAs(User::first())->put(route('admin.biro.update', $biro), [
            'nama' => $biro->nama, 'is_aktif' => 0,
        ])->assertSessionHasNoErrors();
        $this->assertFalse($biro->fresh()->is_aktif);
        $this->get('/biro')->assertDontSee($biro->nama);
        $response = $this->get(route('biro.show', $biro))->assertOk()->assertSee('Arsip');
        $this->assertCount(0, $response->viewData('biro')->kepengurusan);
        if ($kegiatan) {
            $this->assertSame($snapshot, $kegiatan->fresh()->biro_nama);
        }
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_tipe' => 'biro', 'subjek_id' => $biro->id, 'aksi' => 'perubahan']);
    }

    public function test_settings_audit_does_not_record_values_or_unknown_secrets(): void
    {
        $this->seed();
        $this->actingAs(User::first())->put(route('admin.settings.update'), [
            'nama_rayon' => 'Nama Rayon Baru', 'R2_SECRET_ACCESS_KEY' => 'secret-must-not-be-saved',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('riwayat_aktivitas', ['subjek_tipe' => 'settings', 'aksi' => 'perubahan']);
        $this->assertDatabaseMissing('settings', ['key' => 'R2_SECRET_ACCESS_KEY']);
        $this->get(route('admin.riwayat.index'))->assertDontSee('secret-must-not-be-saved')->assertDontSee('Nama Rayon Baru');
    }
}
