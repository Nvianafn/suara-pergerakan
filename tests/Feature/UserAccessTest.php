<?php

namespace Tests\Feature;

use App\Models\Biro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_biro_account_requires_biro_and_denied_access_is_logged(): void
    {
        $this->seed();
        $this->actingAs(User::first());
        $data = ['name' => 'Admin Biro', 'email' => 'biro@example.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin_biro', 'is_active' => 1];
        $this->post(route('admin.users.store'), $data)->assertSessionHasErrors('biro_id');
        $this->post(route('admin.users.store'), $data + ['biro_id' => Biro::first()->id])->assertSessionHasNoErrors();
        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame('admin_biro', $user->role);
        $this->assertTrue($user->must_change_password);
        $this->actingAs($user)->put(route('password.update'), ['current_password' => 'password123', 'password' => 'owner-password', 'password_confirmation' => 'owner-password'])->assertSessionHasNoErrors();
        $user->refresh();
        $this->actingAs($user)->get('/admin/riwayat')->assertForbidden();
        $this->assertDatabaseHas('riwayat_aktivitas', ['user_id' => $user->id, 'aksi' => 'penolakan']);
    }

    public function test_inactive_account_cannot_login_or_use_existing_session(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_last_active_super_admin_cannot_be_demoted_or_disabled(): void
    {
        $this->seed();
        $user = User::first();
        $data = ['name' => $user->name, 'email' => $user->email, 'role' => 'admin', 'is_active' => 1];
        $this->actingAs($user)->put(route('admin.users.update', $user), $data)->assertSessionHasErrors('role');
        $data['role'] = 'super_admin';
        $data['is_active'] = 0;
        $this->put(route('admin.users.update', $user), $data)->assertSessionHasErrors('role');
        $this->assertTrue($user->fresh()->is_active);
        $this->assertSame('super_admin', $user->fresh()->role);
    }
}
