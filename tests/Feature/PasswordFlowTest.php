<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_temporary_password_blocks_cms_until_owner_changes_it(): void
    {
        $this->seed();
        $user = User::first();
        $user->update(['must_change_password' => true]);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('password.change'));
        $this->post(route('admin.karya.store'), [])->assertRedirect(route('password.change'));
        $this->get(route('password.change'))->assertOk();
        $this->put(route('password.update'), ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('current_password');
        $this->put(route('password.update'), ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasNoErrors()->assertRedirect(route('admin.dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_reset_email_token_is_single_use_and_inactive_users_are_excluded(): void
    {
        $this->seed();
        Notification::fake();
        $user = User::first();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'reset-password', 'password_confirmation' => 'reset-password'];
        $this->post(route('password.store'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('reset-password', $user->fresh()->password));
        $this->assertTrue($user->fresh()->must_change_password);
        $this->post(route('password.store'), $data)->assertSessionHasErrors('email');
        $user->update(['is_active' => false]);
        Notification::fake();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertNothingSent();
        $data['token'] = Password::createToken($user);
        $this->post(route('password.store'), $data)->assertSessionHasErrors('email');
    }
}
