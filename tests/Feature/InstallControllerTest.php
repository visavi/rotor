<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Установщик доступен только пока сайт не установлен
        $this->overrideSetting('app_installed', 0);
    }

    public function testAccountFormIsShown(): void
    {
        $this->get('/install/account')->assertOk();
    }

    public function testAccountIsForbiddenWhenInstalled(): void
    {
        $this->overrideSetting('app_installed', 1);

        $this->get('/install/account')->assertForbidden();
    }

    public function testCreatesBossAccount(): void
    {
        $response = $this->post('/install/account', [
            'login'     => 'installboss',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'installboss@example.com',
        ]);

        $response->assertRedirect('/install/finish');

        $user = User::query()->where('login', 'installboss')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::BOSS, $user->level);
        $this->assertAuthenticatedAs($user);
    }

    public function testShortLoginFails(): void
    {
        $response = $this->post('/install/account', [
            'login'     => 'ab',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'installboss@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['login' => 'ab']);

        $response->assertSessionHasErrors('login');
    }

    public function testMismatchedPasswordsFail(): void
    {
        $response = $this->post('/install/account', [
            'login'     => 'installboss',
            'password'  => 'secret123',
            'password2' => 'other-password',
            'email'     => 'installboss@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['login' => 'installboss']);

        $response->assertSessionHasErrors('password2');
    }

    public function testDuplicateLoginFails(): void
    {
        User::factory()->create([
            'login'     => 'installboss',
            'email'     => 'taken@example.com',
            'timebonus' => now(),
        ]);

        $response = $this->post('/install/account', [
            'login'     => 'installboss',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'installboss@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'installboss@example.com']);

        $response->assertSessionHasErrors('login');
    }

    public function testErrorsAreShownOnFormAfterRedirect(): void
    {
        $response = $this->from('/install/account')
            ->post('/install/account', [
                'login'     => 'ab',
                'password'  => 'secret123',
                'password2' => 'secret123',
                'email'     => 'installboss@example.com',
            ]);

        $response->assertRedirect('/install/account');

        $this->get('/install/account')
            ->assertOk()
            ->assertSee('is-invalid')
            ->assertSee('value="ab"', false);
    }
}
