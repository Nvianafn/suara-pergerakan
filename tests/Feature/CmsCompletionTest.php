<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_actions_preserve_publication_time_and_media(): void
    {
        $this->seed();
        $work = Karya::firstOrFail();
        $work->update(['status' => 'published', 'thumbnail' => 'karya/retained.webp']);
        $published = $work->fresh()->published_at->toDateTimeString();
        $this->actingAs(User::firstOrFail());
        foreach (['draft', 'publish', 'delete'] as $action) {
            $this->post(route('admin.karya.bulk'), ['action' => $action, 'ids' => [$work->id]])->assertSessionHasNoErrors()->assertRedirect();
            $row = Karya::withTrashed()->findOrFail($work->id);
            $this->assertSame($published, $row->published_at->toDateTimeString());
            $this->assertSame('karya/retained.webp', $row->thumbnail);
        }
        $this->assertSoftDeleted($work);
        $this->assertDatabaseCount('media_cleanup', 0);
    }

    public function test_biro_admin_cannot_use_bulk_actions(): void
    {
        $this->seed();
        $user = User::firstOrFail();
        $user->update(['role' => 'admin_biro', 'biro_id' => Biro::firstOrFail()->id]);
        $this->actingAs($user)->post(route('admin.karya.bulk'), ['action' => 'publish', 'ids' => [Karya::firstOrFail()->id]])->assertForbidden();
    }

    public function test_member_filters_and_activity_filters_are_applied(): void
    {
        $this->seed();
        $member = Anggota::firstOrFail();
        $member->update(['nama_lengkap' => 'Unique member filter']);
        $this->actingAs(User::firstOrFail())->get(route('admin.anggota.index', ['q' => 'Unique member filter', 'status' => $member->status]))
            ->assertOk()->assertViewHas('anggota', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $member->id);
        ActivityLog::record('test-filter', 'test', 1, 'Filter test');
        $this->get(route('admin.riwayat.index', ['subjek_tipe' => 'test-filter', 'user_id' => User::firstOrFail()->id]))
            ->assertOk()->assertViewHas('activities', fn ($rows) => $rows->total() === 1);
    }

    public function test_existing_gallery_caption_updates_are_scoped_to_activity(): void
    {
        $this->seed();
        $activities = Kegiatan::take(2)->get();
        $first = $activities[0];
        $second = $activities[1];
        $own = $first->foto()->create(['path' => 'gallery/own.webp', 'caption' => 'Old', 'urutan' => 1]);
        $foreign = $second->foto()->create(['path' => 'gallery/foreign.webp', 'caption' => 'Untouched', 'urutan' => 1]);
        $payload = $first->only(['judul', 'periode_id', 'biro_id', 'lokasi', 'deskripsi', 'status']);
        $payload['tanggal'] = $first->tanggal->format('Y-m-d');
        $payload['existing_caption'] = [$own->id => 'Updated', $foreign->id => 'Illegal'];
        $this->actingAs(User::firstOrFail())->put(route('admin.kegiatan.update', $first), $payload)->assertSessionHasNoErrors();
        $this->assertSame('Updated', $own->fresh()->caption);
        $this->assertSame('Untouched', $foreign->fresh()->caption);
    }

    public function test_empty_installation_guides_admin_to_create_period(): void
    {
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'password', 'role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertSee('Mulai dari sini');
        foreach (['admin.karya.create', 'admin.kegiatan.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.dashboard'))->assertSessionHasErrors('periode_id');
        }
    }
}
