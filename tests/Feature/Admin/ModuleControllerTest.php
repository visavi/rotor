<?php

namespace Tests\Feature\Admin;

use App\Models\ModuleRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    private const FIXTURE = 'TestFixture';

    private const FIXTURE_VERSION = '1.2.3';

    private User $boss;

    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_module']);

        // Модули лежат вне репозитория (modules/.gitignore), в CI каталог пуст —
        // список строится по диску, поэтому тест кладёт туда свой модуль
        $this->fixturePath = base_path('modules/' . self::FIXTURE);

        if (! is_dir($this->fixturePath)) {
            mkdir($this->fixturePath, 0755, true);
        }

        file_put_contents($this->fixturePath . '/module.php', sprintf(
            "<?php\n\nreturn [\n    'name' => 'Модуль-фикстура',\n    'version' => '%s',\n    'description' => '',\n    'author' => '',\n];\n",
            self::FIXTURE_VERSION,
        ));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->fixturePath)) {
            @unlink($this->fixturePath . '/module.php');
            @rmdir($this->fixturePath);
        }

        parent::tearDown();
    }

    private function fixtureFileDate(): string
    {
        return date('Y-m-d', (int) filemtime($this->fixturePath . '/module.php'));
    }

    private function fakeRegistry(string $version, ?string $releasedAt): void
    {
        ModuleRegistry::query()->truncate();

        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            array_filter([
                                'version'     => $version,
                                'requires'    => '',
                                'released_at' => $releasedAt,
                            ], static fn ($value) => $value !== null),
                        ],
                    ],
                ],
            ]),
        ]);

        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);
    }

    public function testIndexFallsBackToFileDate(): void
    {
        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        // Реестр молчит — дата берётся с диска (mtime module.php)
        $response->assertSee('data-released="' . $this->fixtureFileDate() . '"', false);
        $response->assertSee('<option value="released">', false);
    }

    public function testIndexTakesReleaseDateFromRegistry(): void
    {
        $this->fakeRegistry(self::FIXTURE_VERSION, '2020-01-02');

        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        $response->assertSee('data-released="2020-01-02"', false);
        $response->assertSee('02.01.2020');
    }

    public function testIndexIgnoresRegistryDateOfOtherVersion(): void
    {
        // Версии, которой на диске нет: её дата к карточке не относится
        $this->fakeRegistry('999.0.0', '2020-01-02');

        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        $response->assertDontSee('data-released="2020-01-02"', false);
        $response->assertSee('data-released="' . $this->fixtureFileDate() . '"', false);
    }

    public function testMarketplaceShowsReleaseDate(): void
    {
        ModuleRegistry::query()->truncate();

        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => 'Dated',
                        'name'     => 'Модуль с датой',
                        'versions' => [
                            ['version' => '1.0.0', 'requires' => '', 'released_at' => '2020-01-02'],
                        ],
                    ],
                    [
                        'module'   => 'Undated',
                        'name'     => 'Модуль без даты',
                        'versions' => [
                            ['version' => '1.0.0', 'requires' => ''],
                        ],
                    ],
                ],
            ]),
        ]);

        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.marketplace'));

        $response->assertOk();
        $response->assertSee('data-released="2020-01-02"', false);
        $response->assertSee('02.01.2020');
        // Версия без released_at остаётся без даты — в сортировке уходит в конец
        $response->assertSee('data-released=""', false);
        $response->assertSee('<option value="released">', false);
    }
}
