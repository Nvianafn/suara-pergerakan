<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Kepengurusan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KepengurusanStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_bph_is_independent_of_biro_and_pages_render(): void
    {
        $this->seed();
        $this->assertSame(4, Kepengurusan::bph()->whereNull('biro_id')->count());
        $this->actingAs(User::first())->get('/admin/kepengurusan')->assertOk()->assertSee('Badan Pengurus Harian');
        $this->get('/kepengurusan')->assertOk()->assertSee('Ketua Rayon');
    }

    public function test_biro_snapshot_survives_rename(): void
    {
        $this->seed();
        $pengurus = Kepengurusan::where('level', 'ketua_biro')->firstOrFail();
        $name = $pengurus->biro_nama;
        $pengurus->biro->update(['nama' => 'Nama Baru']);
        $pengurus->update(['jabatan' => 'Ketua']);
        $this->assertSame($name, $pengurus->fresh()->biro_nama);
    }

    public function test_same_position_on_different_biro_is_allowed_but_duplicate_is_rejected(): void
    {
        $this->seed();
        $pengurus = Kepengurusan::where('level', 'anggota_biro')->firstOrFail();
        $data = $pengurus->only(['anggota_id', 'periode_id', 'jabatan', 'level', 'biro_id']);
        $data['biro_id'] = Biro::unitBiro()->where('id', '!=', $pengurus->biro_id)->firstOrFail()->id;
        Kepengurusan::create($data);
        $this->expectException(QueryException::class);
        Kepengurusan::create($data);
    }
}
