<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackgroundRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('timeonline', 300);

        $this->user = User::factory()->create(['timebonus' => null, 'money' => 1000]);
    }

    protected function tearDown(): void
    {
        Registry::$backgroundPaths = [];

        parent::tearDown();
    }

    public function testBonusIsNotGrantedOnBackgroundRequest(): void
    {
        $this->actingAs($this->user)
            ->getJson('/messages/new', $this->ajax())
            ->assertOk();

        $this->user->refresh();

        $this->assertSame(1000, (int) $this->user->money);
        $this->assertNull($this->user->timebonus);
    }

    public function testBonusIsStillGrantedOnRegularPage(): void
    {
        $this->actingAs($this->user)->get('/')->assertOk();

        $this->user->refresh();

        $this->assertSame(1000 + (int) setting('bonusmoney'), (int) $this->user->money);
        $this->assertNotNull($this->user->timebonus);
    }

    public function testRegisteredPathIsSkippedByStatistic(): void
    {
        Registry::backgroundPath('/');

        $this->actingAs($this->user)->get('/')->assertOk();

        $this->assertDatabaseCount('online', 0);
    }

    public function testUnregisteredPathIsCountedByStatistic(): void
    {
        $this->actingAs($this->user)->get('/')->assertOk();

        $this->assertDatabaseHas('online', ['user_id' => $this->user->id]);
    }

    public function testRegisteredPathIsSkippedByApiVisit(): void
    {
        Registry::backgroundPath('api/user');

        $user = User::factory()->create(['apikey' => Str::random(32), 'updated_at' => now()->subDay()]);

        $this->getJson('/api/user', ['Authorization' => 'Bearer ' . $user->apikey])->assertOk();

        $this->assertDatabaseCount('online', 0);
        $this->assertTrue($user->fresh()->updated_at->isYesterday());
    }

    /**
     * @return array<string, string>
     */
    private function ajax(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }
}
