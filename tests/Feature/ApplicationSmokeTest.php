<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_after_framework_upgrade(): void
    {
        $this->seed();

        foreach (['/', '/tentang', '/biro', '/kepengurusan', '/kegiatan', '/karya', '/kontak', '/login'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_guests_are_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_login_creates_an_authenticated_session(): void
    {
        $this->seed();

        $this->post('/login', [
            'email' => 'admin@pmii-saintek.id',
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticated();
        $this->get('/admin')->assertOk();
    }
}
