<?php

namespace Tests\Feature\User;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RecoveryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('captcha_type', 'disable');

        Mail::fake();
    }

    public function testRecoverySendsLink(): void
    {
        User::factory()->create(['login' => 'forgetful', 'email' => 'forgetful@example.com']);

        $this->post('/recovery', ['user' => 'forgetful'])
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('password_resets', ['email' => 'forgetful@example.com']);
    }

    public function testUnknownUserIsFieldError(): void
    {
        // Раньше вместо формы с ошибкой открывалась отдельная страница
        $this->from('/recovery')
            ->post('/recovery', ['user' => 'nobody'])
            ->assertRedirect('/recovery')
            ->assertSessionHasErrors(['user' => __('validator.user')]);
    }

    public function testUserWithoutEmailFails(): void
    {
        User::factory()->create(['login' => 'no_mail', 'email' => null]);

        $this->post('/recovery', ['user' => 'no_mail'])
            ->assertSessionHasErrors(['user' => __('users.email_not_attached')]);
    }

    public function testRepeatWithinHourFails(): void
    {
        User::factory()->create(['login' => 'forgetful', 'email' => 'forgetful@example.com']);

        PasswordReset::query()->create([
            'email'      => 'forgetful@example.com',
            'token'      => 'existing-token',
            'created_at' => now(),
        ]);

        $this->post('/recovery', ['user' => 'forgetful'])
            ->assertSessionHasErrors(['user' => __('mails.password_recovery_time')]);

        $this->assertDatabaseCount('password_resets', 1);
    }

    public function testAuthorizedUserIsRedirectedFromForm(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/recovery')
            ->assertRedirect('/');
    }
}
