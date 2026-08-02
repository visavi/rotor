<?php

namespace Tests\Feature;

use App\Models\Ban;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('captcha_type', 'disable');
    }

    public function testSearchPageIsShown(): void
    {
        $this->get('/search')->assertOk();
    }

    public function testSearchWithShortQueryShowsError(): void
    {
        $response = $this->get('/search?query=ab');

        $response->assertOk();
        $response->assertSee(__('main.request_length'));
    }

    public function testIpbanPageForBannedIp(): void
    {
        Ban::query()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => 0,
            'created_at' => now()->subHour(),
        ]);

        $this->get('/ipban')->assertStatus(429);
    }

    public function testIpbanRedirectsWhenNotBanned(): void
    {
        $this->get('/ipban')->assertRedirect('/');
    }

    public function testIpbanSelfUnbanWithCaptcha(): void
    {
        $ban = Ban::query()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => 0,
            'created_at' => now()->subHour(),
        ]);

        $response = $this->post('/ipban');

        $response->assertRedirect('/');

        $this->assertDatabaseMissing('ban', ['id' => $ban->id]);

        $response->assertSessionHas('success');
    }

    public function testIpbanCannotSelfUnbanWhenBannedByAdmin(): void
    {
        $admin = User::factory()->admin()->create([
            'login'     => 'admin_ipban',
            'timebonus' => now(),
        ]);

        $ban = Ban::query()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => $admin->id,
            'created_at' => now()->subHour(),
        ]);

        $this->post('/ipban')->assertStatus(429);

        $this->assertDatabaseHas('ban', ['id' => $ban->id]);
    }
}
