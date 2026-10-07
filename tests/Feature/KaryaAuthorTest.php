<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Karya;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryaAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_author_modes_are_saved_and_displayed(): void
    {
        $this->seed();
        Setting::put('nama_rayon', 'Rayon Uji');
        $member = Anggota::first();
        $this->actingAs(User::first());
        foreach (['anggota' => $member->nama_lengkap, 'nama_bebas' => 'Penulis Luar', 'anonim' => 'Anonim', 'redaksi' => 'Redaksi Rayon Uji'] as $mode => $expected) {
            $this->post(route('admin.karya.store'), [
                'judul' => 'Karya '.$mode, 'konten' => '<p>Isi</p>', 'tipe' => 'esai', 'status' => 'published',
                'penulis_tipe' => $mode, 'anggota_id' => $mode === 'anggota' ? $member->id : null,
                'penulis_nama' => $mode === 'nama_bebas' ? 'Penulis Luar' : null,
            ])->assertSessionHasNoErrors();
            $work = Karya::where('judul', 'Karya '.$mode)->firstOrFail();
            $this->assertSame($expected, $work->penulis());
            $this->get(route('karya.show', $work))->assertOk()->assertSee($expected);
        }
    }

    public function test_inconsistent_author_data_is_rejected(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $base = ['judul' => 'Tidak valid', 'konten' => 'Isi', 'tipe' => 'esai', 'status' => 'draft'];
        $this->post(route('admin.karya.store'), $base + ['penulis_tipe' => 'anonim', 'anggota_id' => Anggota::first()->id])->assertSessionHasErrors('anggota_id');
        $this->post(route('admin.karya.store'), $base + ['penulis_tipe' => 'nama_bebas'])->assertSessionHasErrors('penulis_nama');
        $this->post(route('admin.karya.store'), $base + ['penulis_tipe' => 'anggota'])->assertSessionHasErrors('anggota_id');
        $this->assertDatabaseMissing('karya', ['judul' => 'Tidak valid']);
    }
}
