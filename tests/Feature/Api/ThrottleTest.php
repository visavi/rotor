<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testAuthIsLimitedByIp(): void
    {
        $credentials = ['login' => 'unknown', 'password' => 'wrong'];

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/auth', $credentials)->assertStatus(401);
        }

        $this->postJson('/api/auth', $credentials)->assertStatus(429);
    }

    public function testApiHasRateLimitHeaders(): void
    {
        $this->getJson('/api/config')
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', 120);
    }
}
