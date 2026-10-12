<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\User;
use App\Services\FeedService;
use App\Support\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedLatestTest extends TestCase
{
    use RefreshDatabase;

    private array $feeds;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Реестр живёт в статике весь прогон: типы модулей возвращаются после теста
        $this->feeds = Registry::$feeds;

        Relation::morphMap([LatestEntry::$morphName => LatestEntry::class]);
        Registry::feed(LatestEntry::class, [
            'with'  => [],
            'view'  => 'feeds._comments',
            'scope' => static fn ($query) => $query->where('text', '!=', 'Скрытая'),
        ]);

        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Registry::$feeds = $this->feeds;

        parent::tearDown();
    }

    public function testReturnsLatestVisibleRecordsOfSelectedTypes(): void
    {
        $this->createEntry('Старая', now()->subDays(3));
        $this->createEntry('Средняя', now()->subDays(2));
        $this->createEntry('Скрытая', now()->subHour());
        $this->createEntry('Новая', now()->subDay());

        $latest = (new FeedService())->getLatest([LatestEntry::$morphName], 2);

        $this->assertSame(['Новая', 'Средняя'], $latest->pluck('text')->all());
    }

    public function testIgnoresFeedSettings(): void
    {
        // Настройки ленты — для самой ленты: выключенный в ней тип и порог рейтинга сюда не действуют
        $this->overrideSetting('feed_' . LatestEntry::$morphName . '_show', 0);
        $this->overrideSetting('feed_' . LatestEntry::$morphName . '_rating', 100);
        $this->createEntry('С нулевым рейтингом', now());

        $latest = (new FeedService())->getLatest([LatestEntry::$morphName], 5);

        $this->assertSame(['С нулевым рейтингом'], $latest->pluck('text')->all());
    }

    public function testSkipsUnselectedAndUnknownTypes(): void
    {
        $this->createEntry('Запись', now());

        $service = new FeedService();

        $this->assertTrue($service->getLatest(['comments'], 5)->isEmpty());
        $this->assertTrue($service->getLatest(['missing'], 5)->isEmpty());
    }

    private function createEntry(string $text, mixed $createdAt): void
    {
        $entry = LatestEntry::query()->create([
            'relate_type' => 'test',
            'relate_id'   => 1,
            'text'        => $text,
            'user_id'     => $this->user->id,
            'rating'      => 0,
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
            'created_at'  => $createdAt,
        ]);

        Feed::query()->insert([
            'relate_type' => LatestEntry::$morphName,
            'relate_id'   => $entry->getKey(),
            'created_at'  => $createdAt,
        ]);
    }
}

/**
 * Запись своего типа ленты на таблице comments: тип не зависит от модулей
 */
class LatestEntry extends Model
{
    public static string $morphName = 'test-latest';

    public $timestamps = false;

    protected $table = 'comments';

    protected $guarded = [];
}
