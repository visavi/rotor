<?php

namespace Tests\Feature;

use App\Models\Error;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckThrottleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Окно счётчика — минута, лимит запросов за неё
     */
    private const int LIMIT = 30;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('doslimit', self::LIMIT);
        $this->overrideSetting('errorlog', 1);
    }

    public function testGuestIsAutobannedAfterLimit(): void
    {
        // Лимит разрешает ровно LIMIT запросов, банится следующий
        for ($i = 0; $i < self::LIMIT; $i++) {
            $this->get('/')->assertOk();
        }

        $response = $this->get('/');

        $response->assertStatus(429);

        $this->assertDatabaseHas('ban', ['ip' => '127.0.0.1', 'user_id' => null]);
        $this->assertSame(1, Error::query()->where('code', 666)->count());
    }

    public function testAdminIsNotAutobanned(): void
    {
        $admin = User::factory()->admin()->create([
            'login'     => 'admin_throttle',
            'timebonus' => now(),
        ]);

        $this->actingAs($admin);

        for ($i = 0; $i <= self::LIMIT; $i++) {
            $response = $this->get('/');
        }

        $response->assertOk();

        $this->assertDatabaseCount('ban', 0);
    }
}
