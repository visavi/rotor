<?php

namespace Tests\Feature\Api;

use App\Models\Online;
use App\Models\User;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Статистика кешируется на пять минут, между тестами кэш нужно сбрасывать
        Cache::flush();
    }

    public function testStatsAreOpenForGuests(): void
    {
        User::factory()->count(2)->create();

        $response = $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonStructure([
                'users'    => ['total', 'today', 'admins'],
                'online'   => ['users', 'guests', 'total'],
                'sections' => [],
            ]);

        $this->assertSame(2, $response->json('users.total'));
        $this->assertSame(2, $response->json('users.today'));
        // Гостю блок про пользователя не положен
        $this->assertArrayNotHasKey('user', $response->json());
    }

    public function testAdminsAreCounted(): void
    {
        User::factory()->create(['level' => User::ADMIN]);
        User::factory()->create(['level' => User::USER]);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('users.admins', 1);
    }

    public function testOnlineIsCounted(): void
    {
        $user = User::factory()->create();

        Online::query()->create([
            'ip'         => '127.0.0.1',
            'brow'       => 'test',
            'uid'        => 'session1',
            'user_id'    => $user->id,
            'updated_at' => now(),
        ]);

        Online::query()->create([
            'ip'         => '127.0.0.2',
            'brow'       => 'test',
            'uid'        => 'session2',
            'user_id'    => null,
            'updated_at' => now(),
        ]);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('online.users', 1)
            ->assertJsonPath('online.guests', 1)
            ->assertJsonPath('online.total', 2);
    }

    public function testSectionCounterComesFromModule(): void
    {
        // Ядро о разделах модулей не знает — счётчик приходит от самого модуля
        Registry::stat('demo', static fn (): int => 42);

        $this->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('sections.demo', 42);
    }

    public function testUserBlockNeedsToken(): void
    {
        $user = User::factory()->create([
            'apikey'    => Str::random(32),
            'newprivat' => 3,
            'point'     => 100,
        ]);

        $this->getJson('/api/stats', ['Authorization' => 'Bearer ' . $user->apikey])
            ->assertOk()
            ->assertJsonPath('user.new_messages', 3)
            ->assertJsonPath('user.point', 100);
    }
}
