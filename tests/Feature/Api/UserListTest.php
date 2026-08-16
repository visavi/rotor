<?php

namespace Tests\Feature\Api;

use App\Models\Online;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('userlist', 10);
        $this->overrideSetting('onlinelist', 10);
    }

    public function testUserListIsOpenForGuests(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data' => [['login', 'point', 'rating']], 'links', 'meta'])
            ->assertJsonCount(3, 'data');
    }

    public function testAdminsAreFiltered(): void
    {
        User::factory()->create(['level' => User::USER]);
        $admin = User::factory()->create(['level' => User::ADMIN]);

        $this->getJson('/api/users?type=admins')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login', $admin->login);
    }

    public function testUsersAreSearchedByLogin(): void
    {
        $user = User::factory()->create(['login' => 'searchable']);
        User::factory()->create(['login' => 'another']);

        $this->getJson('/api/users?user=search')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login', $user->login);
    }

    public function testSortByPointsRespectsOrder(): void
    {
        $rich = User::factory()->create(['point' => 100]);
        $poor = User::factory()->create(['point' => 1]);

        $this->getJson('/api/users?sort=point&order=asc')
            ->assertOk()
            ->assertJsonPath('data.0.login', $poor->login);

        $this->getJson('/api/users?sort=point&order=desc')
            ->assertOk()
            ->assertJsonPath('data.0.login', $rich->login);
    }

    public function testSearchNeedsTwoCharacters(): void
    {
        User::factory()->create(['login' => 'visavi']);

        $this->getJson('/api/users/search?query=v')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/users/search?query=vi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login', 'visavi');
    }

    public function testOnlineListsUsersAndCountsGuests(): void
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

        $this->getJson('/api/online')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.login', $user->login)
            ->assertJsonPath('meta.users', 1)
            ->assertJsonPath('meta.guests', 1);
    }

    public function testOnlineKeepsOneVisitPerUser(): void
    {
        $user = User::factory()->create();

        // Два захода одного пользователя: в списке он должен быть один раз
        Online::query()->create([
            'ip'         => '127.0.0.1',
            'brow'       => 'first',
            'uid'        => 'session1',
            'user_id'    => $user->id,
            'updated_at' => now()->subMinute(),
        ]);

        Online::query()->create([
            'ip'         => '127.0.0.1',
            'brow'       => 'second',
            'uid'        => 'session2',
            'user_id'    => $user->id,
            'updated_at' => now(),
        ]);

        $this->getJson('/api/online')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.users', 1);
    }
}
