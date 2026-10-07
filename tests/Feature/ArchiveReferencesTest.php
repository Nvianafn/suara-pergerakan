<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\Pembina;
use App\Models\Periode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArchiveReferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_snapshot_survives_rename_and_only_changes_when_biro_changes(): void
    {
        $this->seed();
        $biro = Biro::unitBiro()->firstOrFail();
        foreach ([Kegiatan::first(), Karya::first()] as $content) {
            $content->update(['biro_id' => $biro->id]);
            $this->assertSame($biro->nama, $content->fresh()->biro_nama);
        }
        $old = $biro->nama;
        $biro->update(['nama' => 'Nama Biro Baru', 'is_aktif' => false]);
        foreach ([Kegiatan::first(), Karya::first()] as $content) {
            $content->update(['judul' => 'Judul diperbarui']);
            $this->assertSame($old, $content->fresh()->biro_nama);
            $other = Biro::unitBiro()->where('id', '!=', $biro->id)->firstOrFail();
            $content->update(['biro_id' => $other->id]);
            $this->assertSame($other->nama, $content->fresh()->biro_nama);
        }
    }

    public function test_database_blocks_direct_deletion_of_referenced_period_instead_of_cascading(): void
    {
        $this->seed();
        $periode = Periode::whereHas('kepengurusan')->firstOrFail();
        try {
            DB::table('periode')->where('id', $periode->id)->delete();
            $this->fail('Foreign key must prevent deletion');
        } catch (QueryException) {
            $this->assertDatabaseHas('periode', ['id' => $periode->id]);
            $this->assertGreaterThan(0, $periode->kepengurusan()->count());
        }
    }

    public function test_structure_archive_url_uses_requested_period_and_slug_stays_stable(): void
    {
        $this->seed();
        $periode = Periode::where('is_aktif', false)->firstOrFail();
        $profil = Pembina::create(['nama_lengkap' => 'Pembina Arsip Khusus']);
        $periode->pembina()->attach($profil->id);
        $slug = $periode->slug;
        $periode->update(['nama' => 'Nama Periode Baru']);
        $this->assertSame($slug, $periode->fresh()->slug);
        $this->get(route('kepengurusan.arsip', $slug))->assertOk()->assertSee('Pembina Arsip Khusus');
        $this->get('/kepengurusan')->assertDontSee('Pembina Arsip Khusus');
        $this->get('/kepengurusan/tidak-ada')->assertNotFound();
    }

    public function test_activity_filter_uses_period_archive_and_snapshot_label(): void
    {
        $this->seed();
        $periode = Periode::first();
        $biro = Biro::unitBiro()->first();
        $old = $biro->nama;
        $kegiatan = Kegiatan::create(['judul' => 'Kegiatan Arsip Terpilih', 'tanggal' => '2026-10-07', 'status' => 'published', 'periode_id' => $periode->id, 'biro_id' => $biro->id]);
        $biro->update(['nama' => 'Biro Nama Terkini']);
        $this->get('/kegiatan?periode='.$periode->slug)->assertOk()->assertSee($kegiatan->judul)->assertSee(str_replace('Biro ', '', $old));
        $other = Periode::where('id', '!=', $periode->id)->first();
        $this->get('/kegiatan?periode='.$other->slug)->assertDontSee($kegiatan->judul);
        $this->get(route('kegiatan.show', $kegiatan))->assertSee($old)->assertDontSee('Biro Nama Terkini');
    }
}
