<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_creates_only_first_admin_with_forced_password_change(): void
    {
        config(['bootstrap_admin.name' => 'Owner', 'bootstrap_admin.email' => 'owner@example.test', 'bootstrap_admin.password' => 'temporary-password']);
        $this->artisan('app:buat-super-admin', ['--no-interaction' => true])->assertSuccessful();
        $user = User::firstOrFail();
        $this->assertSame('super_admin', $user->role);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('temporary-password', $user->password));
        $user->update(['is_active' => false]);
        $this->artisan('app:buat-super-admin', ['--no-interaction' => true])->assertFailed();
        $this->assertSame(1, User::count());
    }

    public function test_missing_secrets_create_no_account(): void
    {
        config(['bootstrap_admin.name' => null, 'bootstrap_admin.email' => null, 'bootstrap_admin.password' => null]);
        $this->artisan('app:buat-super-admin', ['--no-interaction' => true])->assertFailed();
        $this->assertSame(0, User::count());
    }

    public function test_production_seeder_has_no_dummy_accounts_and_preserves_settings(): void
    {
        $this->app->instance('env', 'production');
        Setting::put('nama_rayon', 'Nama asli');
        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        $this->assertSame(0, User::count());
        $this->assertDatabaseCount('anggota', 0);
        $this->assertDatabaseCount('periode', 0);
        $this->assertSame('Nama asli', Setting::get('nama_rayon'));
        $this->assertDatabaseHas('settings', ['key' => 'og_image', 'value' => '']);
    }
}
