<?php

namespace Tests\Feature\Api;

use App\Models\Online;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('timeonline', 300);

        $this->user = User::factory()->create(['apikey' => Str::random(32), 'updated_at' => now()->subDay()]);
    }

    public function testRequestWithTokenUpdatesLastVisit(): void
    {
        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->assertTrue($this->user->fresh()->updated_at->isToday());
    }

    public function testRequestWithTokenMarksUserOnline(): void
    {
        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->assertDatabaseHas('online', ['user_id' => $this->user->id]);
    }

    public function testVisitIsThrottled(): void
    {
        $this->getJson('/api/user', $this->headers())->assertOk();

        // Откатываем время визита: если троттлинг не сработает, оно обновится снова
        $this->rollbackVisit();

        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->assertTrue($this->user->fresh()->updated_at->isYesterday());
    }

    public function testVisitIsSavedAgainAfterThrottleExpires(): void
    {
        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->rollbackVisit();
        Cache::flush();

        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->assertTrue($this->user->fresh()->updated_at->isToday());
    }

    public function testOptionalTokenAlsoSavesVisit(): void
    {
        $this->getJson('/api/feed', $this->headers())->assertOk();

        $this->assertTrue($this->user->fresh()->updated_at->isToday());
        $this->assertDatabaseHas('online', ['user_id' => $this->user->id]);
    }

    public function testGuestDoesNotSaveVisit(): void
    {
        $this->getJson('/api/feed')->assertOk();

        $this->assertDatabaseCount('online', 0);
        $this->assertTrue($this->user->fresh()->updated_at->isYesterday());
    }

    public function testInvalidTokenDoesNotSaveVisit(): void
    {
        $this->getJson('/api/user', ['Authorization' => 'Bearer wrongtoken'])->assertStatus(401);

        $this->assertDatabaseCount('online', 0);
    }

    public function testBannedUserDoesNotSaveVisit(): void
    {
        $this->user->update(['level' => User::BANNED, 'updated_at' => now()->subDay()]);

        $this->getJson('/api/user', $this->headers())->assertStatus(403);

        $this->assertDatabaseCount('online', 0);
        $this->assertTrue($this->user->fresh()->updated_at->isYesterday());
    }

    public function testStaleOnlineRecordsAreCleaned(): void
    {
        Online::query()->create([
            'uid'        => md5('stale'),
            'ip'         => '127.0.0.2',
            'brow'       => 'Old browser',
            'user_id'    => null,
            'updated_at' => now()->subHour(),
        ]);

        $this->getJson('/api/user', $this->headers())->assertOk();

        $this->assertDatabaseMissing('online', ['uid' => md5('stale')]);
        $this->assertDatabaseHas('online', ['user_id' => $this->user->id]);
    }

    /**
     * Откат времени визита мимо модели — она уже держит это значение и UPDATE не ушёл бы
     */
    private function rollbackVisit(): void
    {
        User::query()->whereKey($this->user->id)->update(['updated_at' => now()->subDay()]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }
}
