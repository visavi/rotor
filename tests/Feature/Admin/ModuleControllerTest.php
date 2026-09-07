<?php

namespace Tests\Feature\Admin;

use App\Models\Module;
use App\Models\ModuleRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

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
            "<?php\n\nreturn [\n    'name' => 'Модуль-фикстура',\n    'version' => '%s',\n    'description' => '',\n    'author' => '',\n    'homepage' => '',\n];\n",
            self::FIXTURE_VERSION,
        ));
    }

    protected function tearDown(): void
    {
        // Миграция модуля — DDL: неявный COMMIT переживает откат RefreshDatabase,
        // поэтому таблицу и запись о ней убираем руками. Проверка hasTable здесь
        // обязательна: dropIfExists шлёт DROP и когда таблицы нет, а этот DDL
        // рвал бы транзакцию в каждом тесте — прогон замедлялся вчетверо
        if (Schema::hasTable('test_fixture_items')) {
            Schema::drop('test_fixture_items');
            DB::table('migrations')->where('migration', 'like', '%create_test_fixture_items_table')->delete();
        }

        if (is_dir($this->fixturePath)) {
            @unlink($this->fixturePath . '/module.php');
            File::deleteDirectory($this->fixturePath . '/database');
            @rmdir($this->fixturePath);
        }

        parent::tearDown();
    }

    /**
     * Кладёт в модуль-фикстуру миграцию, создающую таблицу
     */
    private function addFixtureMigration(): void
    {
        $path = $this->fixturePath . '/database/migrations';

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($path . '/2020_01_01_000000_create_test_fixture_items_table.php', <<<'PHP'
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('test_fixture_items', function (Blueprint $table) {
                    $table->id();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('test_fixture_items');
            }
        };
        PHP);
    }

    private function fixtureFileDate(): string
    {
        return date('Y-m-d', (int) filemtime($this->fixturePath . '/module.php'));
    }

    private function fakeRegistry(string $version, ?string $releasedAt): void
    {
        ModuleRegistry::query()->delete();

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
        ModuleRegistry::query()->delete();

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

    /**
     * Собирает ZIP с модулем-фикстурой заданной версии
     */
    private function makeZip(string $version, ?string $requires = null): string
    {
        $path = storage_path('app/temp/test_module_' . uniqid() . '.zip');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $config = sprintf(
            "<?php\n\nreturn [\n    'name' => 'Модуль-фикстура',\n    'version' => '%s',\n    'description' => '',\n    'author' => '',\n    'homepage' => '',\n%s];\n",
            $version,
            $requires ? sprintf("    'requires' => '%s',\n", $requires) : '',
        );

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString(self::FIXTURE . '/module.php', $config);
        $zip->close();

        return $path;
    }

    /**
     * Подменяет ответ по ссылке на архив
     */
    private function fakeDownload(string $zipPath): void
    {
        Http::fake([
            'files.example.com/*' => Http::response(file_get_contents($zipPath)),
        ]);
    }

    public function testDownloadAppliesUpdateOfInstalledModule(): void
    {
        Module::query()->create(['name' => self::FIXTURE, 'version' => self::FIXTURE_VERSION, 'active' => false]);

        $zip = $this->makeZip('2.0.0');
        $this->fakeDownload($zip);

        $response = $this->actingAs($this->boss)->post(route('admin.modules.download'), [
            'url' => 'https://files.example.com/module.zip',
        ]);

        $response->assertRedirect('/admin/modules/module?module=' . self::FIXTURE);
        $this->assertContains(__('admin.modules.module_success_updated'), (array) session('success'));

        // Файлы распакованы и версия зафиксирована в БД одним действием
        $this->assertSame('2.0.0', Module::query()->where('name', self::FIXTURE)->value('version'));

        @unlink($zip);
    }

    public function testDownloadDoesNotApplyIncompatibleUpdate(): void
    {
        Module::query()->create(['name' => self::FIXTURE, 'version' => self::FIXTURE_VERSION, 'active' => false]);

        $zip = $this->makeZip('2.0.0', '999.0.0');
        $this->fakeDownload($zip);

        $response = $this->actingAs($this->boss)
            ->from('/admin/modules/module?module=' . self::FIXTURE)
            ->post(route('admin.modules.download'), ['url' => 'https://files.example.com/module.zip']);

        $response->assertSessionHas('danger');

        // Версия в БД не поднята: обновление ждёт совместимого движка
        $this->assertSame(self::FIXTURE_VERSION, Module::query()->where('name', self::FIXTURE)->value('version'));

        @unlink($zip);
    }

    public function testDownloadInstallsNotInstalledModule(): void
    {
        // Кнопка в каталоге называется «Установить» — скачивание её и выполняет
        $zip = $this->makeZip('2.0.0');
        $this->fakeDownload($zip);

        $response = $this->actingAs($this->boss)->post(route('admin.modules.download'), [
            'url' => 'https://files.example.com/module.zip',
        ]);

        $response->assertRedirect('/admin/modules/module?module=' . self::FIXTURE);
        $this->assertContains(__('admin.modules.module_success_installed'), (array) session('success'));
        $this->assertSame('2.0.0', Module::query()->where('name', self::FIXTURE)->value('version'));

        @unlink($zip);
    }

    public function testDownloadDoesNotInstallIncompatibleModule(): void
    {
        // Несовместимый и ещё не установленный: файлы легли, но в систему не попал
        $zip = $this->makeZip('2.0.0', '999.0.0');
        $this->fakeDownload($zip);

        $response = $this->actingAs($this->boss)->post(route('admin.modules.download'), [
            'url' => 'https://files.example.com/module.zip',
        ]);

        $response->assertSessionHas('danger');
        $this->assertFalse(Module::query()->where('name', self::FIXTURE)->exists());

        @unlink($zip);
    }

    public function testMarketplaceInstallsLocallyPresentModule(): void
    {
        // Файлы уже на диске, в системе модуля нет — кнопка ставит, а не ведёт на страницу
        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => self::FIXTURE_VERSION, 'requires' => ''],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.marketplace'));

        $response->assertOk();
        $response->assertSee('name="module" value="' . self::FIXTURE . '"', false);
        $response->assertSee(route('admin.modules.install'), false);
    }

    public function testModulePageShowsUpdateButton(): void
    {
        Module::query()->create(['name' => self::FIXTURE, 'version' => self::FIXTURE_VERSION, 'active' => true]);

        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => '2.0.0', 'requires' => '', 'download_url' => 'https://files.example.com/module.zip'],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.module', ['module' => self::FIXTURE]));

        $response->assertOk();
        $response->assertSee(__('admin.modules.update_to', ['version' => '2.0.0']));
        $response->assertSee('https://files.example.com/module.zip', false);
    }

    public function testModulePageHidesDownloadWhenSameVersionAlreadyOnDisk(): void
    {
        // На диске лежит распакованная 1.2.3, в БД ещё 1.0.0 — реестр предлагает
        // ту же 1.2.3: качать нечего, достаточно «Применить обновление»
        Module::query()->create(['name' => self::FIXTURE, 'version' => '1.0.0', 'active' => true]);

        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => self::FIXTURE_VERSION, 'requires' => '', 'download_url' => 'https://files.example.com/module.zip'],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.module', ['module' => self::FIXTURE]));

        $response->assertOk();
        // Кнопка обновления есть, но ведёт на применение, а не на скачивание
        $response->assertSee(__('admin.modules.update_to', ['version' => self::FIXTURE_VERSION]));
        $response->assertSee(route('admin.modules.install'), false);
        $response->assertDontSee('https://files.example.com/module.zip', false);
    }

    public function testMarketplaceOffersApplyInsteadOfRedownload(): void
    {
        // На диске распакована 1.2.3, в БД ещё 1.0.0, в реестре та же 1.2.3:
        // качать нечего — маркетплейс предлагает применить
        Module::query()->create(['name' => self::FIXTURE, 'version' => '1.0.0', 'active' => true]);

        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => self::FIXTURE_VERSION, 'requires' => '', 'download_url' => 'https://files.example.com/module.zip'],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.marketplace'));

        $response->assertOk();
        // Кнопка обновления есть, но ведёт на применение, а не на скачивание
        $response->assertSee(__('admin.modules.update_to', ['version' => self::FIXTURE_VERSION]));
        $response->assertSee(route('admin.modules.install'), false);
        $response->assertDontSee('https://files.example.com/module.zip', false);
    }

    public function testMarketplaceStillOffersDownloadOfNewerVersion(): void
    {
        // В реестре версия новее той, что лежит на диске — качать надо
        Module::query()->create(['name' => self::FIXTURE, 'version' => self::FIXTURE_VERSION, 'active' => true]);

        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => '2.0.0', 'requires' => '', 'download_url' => 'https://files.example.com/module.zip'],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.marketplace'));

        $response->assertOk();
        $response->assertSee(__('admin.modules.update_to', ['version' => '2.0.0']));
        $response->assertSee('https://files.example.com/module.zip', false);
    }

    public function testUpdateOfDisabledModuleRunsMigrationsWithoutPublishing(): void
    {
        // Выключенный модуль: файлы в public не возвращаем, но схему подтягиваем —
        // версия в БД поднимается, и таблицы модуля не должны от неё отставать
        Module::query()->create(['name' => self::FIXTURE, 'version' => '1.0.0', 'active' => false]);
        $this->addFixtureMigration();

        $this->assertFalse(Schema::hasTable('test_fixture_items'));

        $response = $this->actingAs($this->boss)->post(route('admin.modules.install'), [
            'module' => self::FIXTURE,
            'update' => 1,
        ]);

        $response->assertRedirect('admin/modules/module?module=' . self::FIXTURE);

        $this->assertTrue(Schema::hasTable('test_fixture_items'));
        $this->assertSame(self::FIXTURE_VERSION, Module::query()->where('name', self::FIXTURE)->value('version'));
        $this->assertFalse(Module::query()->where('name', self::FIXTURE)->value('active'));
    }

    public function testModulePageShowsSingleUpdateButton(): void
    {
        // В БД 1.0.0, на диске непринятая 1.2.3, в реестре 2.0.0 — кнопка одна:
        // сначала закрываем расхождение диска и БД, реестр предложится следующим шагом
        Module::query()->create(['name' => self::FIXTURE, 'version' => '1.0.0', 'active' => true]);

        ModuleRegistry::query()->delete();
        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => self::FIXTURE,
                        'name'     => 'Модуль-фикстура',
                        'versions' => [
                            ['version' => '2.0.0', 'requires' => '', 'download_url' => 'https://files.example.com/module.zip'],
                        ],
                    ],
                ],
            ]),
        ]);
        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.module', ['module' => self::FIXTURE]));

        $response->assertOk();
        $response->assertSee(__('admin.modules.update_to', ['version' => self::FIXTURE_VERSION]));
        $response->assertDontSee(__('admin.modules.update_to', ['version' => '2.0.0']));
        $response->assertDontSee('https://files.example.com/module.zip', false);
    }
}
