<?php

namespace Tests\Feature\Api;

use App\Http\Resources\SearchResource;
use App\Models\Search;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testSearchIsAvailableForGuests(): void
    {
        $this->getJson('/api/search?query=шушенское')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonPath('query', 'шушенское')
            ->assertJsonPath('sort', 'relevance')
            ->assertJsonPath('type', null);
    }

    public function testShortQueryIsRejected(): void
    {
        $this->getJson('/api/search?query=ab')
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function testUnknownTypeAndSortAreReset(): void
    {
        $this->getJson('/api/search?query=шушенское&type=unknown&sort=unknown')
            ->assertOk()
            ->assertJsonPath('type', null)
            ->assertJsonPath('sort', 'relevance');
    }

    public function testKnownTypeIsKept(): void
    {
        $this->getJson('/api/search?query=шушенское&type=users&sort=date')
            ->assertOk()
            ->assertJsonPath('type', 'users')
            ->assertJsonPath('sort', 'date');
    }

    // Выдача fulltext не проверяется: InnoDB обновляет индекс на коммите,
    // а тесты идут в откатываемой транзакции
    public function testResourceBuildsUserResult(): void
    {
        $user = User::factory()->create(['info' => 'Заповедник']);

        $search = new Search([
            'relate_type' => User::$morphName,
            'relate_id'   => $user->id,
            'text'        => 'Заповедник',
            'created_at'  => now(),
        ]);
        $search->setRelation('relate', $user);

        $result = SearchResource::make($search)->toArray(Request::create('/api/search'));

        $this->assertSame(User::$morphName, $result['type']);
        $this->assertSame($user->id, $result['id']);
        $this->assertSame($user->login, $result['title']);
        $this->assertSame(route('users.user', ['login' => $user->login]), $result['url']);
        $this->assertSame($user->login, $result['user']->toArray(Request::create('/api/search'))['login']);
    }
}
