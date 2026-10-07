<?php

namespace Tests\Feature;

use App\Models\Periode;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodeAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::create([
            'name' => $role, 'email' => $role.'@example.test',
            'password' => 'password', 'role' => $role,
        ]);
    }

    private function data(): array
    {
        return ['nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027];
    }

    public function test_admin_cannot_activate_a_period(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post('/admin/periode', $this->data() + ['is_aktif' => true])->assertForbidden();
        $this->assertDatabaseCount('periode', 0);
    }

    public function test_admin_edit_does_not_deactivate_existing_active_period(): void
    {
        $periode = Periode::create($this->data() + ['is_aktif' => true]);
        $this->actingAs($this->userWithRole('admin'))
            ->put('/admin/periode/'.$periode->id, $this->data())->assertRedirect();
        $this->assertTrue($periode->fresh()->is_aktif);
    }

    public function test_super_admin_can_replace_active_period_without_deleting_archive(): void
    {
        $old = Periode::create($this->data() + ['is_aktif' => true]);
        $this->actingAs($this->userWithRole('super_admin'))
            ->post('/admin/periode', $this->data() + ['is_aktif' => true])->assertRedirect();
        $this->assertFalse($old->fresh()->is_aktif);
        $this->assertSame(1, Periode::aktif()->count());
        $this->assertDatabaseCount('periode', 2);
    }

    public function test_database_rejects_two_active_periods(): void
    {
        Periode::create($this->data() + ['is_aktif' => true]);
        $this->expectException(QueryException::class);
        Periode::create($this->data() + ['is_aktif' => true]);
    }

    public function test_active_period_cannot_be_deleted(): void
    {
        $periode = Periode::create($this->data() + ['is_aktif' => true]);
        $this->actingAs($this->userWithRole('super_admin'))
            ->delete('/admin/periode/'.$periode->id)->assertSessionHasErrors('periode');
        $this->assertDatabaseHas('periode', ['id' => $periode->id]);
    }
}
