<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Пароль из фабрики — 'password'
        $this->user = User::factory()->create([
            'login' => 'testuser',
            'email' => 'testuser@example.com',
        ]);
    }

    public function testLoginByLogin(): void
    {
        $response = $this->withSession(['url.intended' => '/stickers'])
            ->post('/login', [
                'login'    => 'testuser',
                'password' => 'password',
            ]);

        $response->assertRedirect('/stickers');
        $response->assertSessionHas('success');
        $this->assertAuthenticatedAs($this->user);
    }

    public function testLoginByEmail(): void
    {
        $response = $this->post('/login', [
            'login'    => 'testuser@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->user);
    }

    public function testLoginWithWrongPassword(): void
    {
        $response = $this->post('/login', [
            'login'    => 'testuser',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('danger');
        $this->assertGuest();
        $this->assertDatabaseHas('floods', ['page' => '/login']);
    }

    public function testLoginRequiresCaptchaAfterFailedAttempt(): void
    {
        // Первая неудачная попытка включает флуд-режим
        $this->post('/login', [
            'login'    => 'testuser',
            'password' => 'wrong-password',
        ]);

        // Верный пароль, но капча не пройдена — вход отклонён
        $response = $this->post('/login', [
            'login'    => 'testuser',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();

        // С капчей вход проходит
        $response = $this->withSession(['protect' => 'abc12'])
            ->post('/login', [
                'login'    => 'testuser',
                'password' => 'password',
                'protect'  => 'abc12',
            ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->user);
    }

    public function testLoginPageRedirectsWhenAuthorized(): void
    {
        $response = $this->actingAs($this->user)->get('/login');

        $response->assertRedirect('/');
        $response->assertSessionHas('danger');
    }

    public function testLoginWithRememberSetsCookie(): void
    {
        $response = $this->post('/login', [
            'login'    => 'testuser',
            'password' => 'password',
            'remember' => 1,
        ]);

        $response->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertAuthenticatedAs($this->user);
    }

    public function testLogout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
