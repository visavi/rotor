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

    private User $boss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_module']);
    }

    public function testIndexFallsBackToFileDate(): void
    {
        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        // Реестр молчит — дата берётся с диска (mtime module.php), она есть у всех
        $response->assertSee('data-released="' . date('Y-m-d', (int) filemtime(base_path('modules/Blog/module.php'))) . '"', false);
        $response->assertSee('<option value="released">', false);
    }

    public function testIndexTakesReleaseDateFromRegistry(): void
    {
        $config = include base_path('modules/Blog/module.php');

        ModuleRegistry::query()->truncate();

        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => 'Blog',
                        'name'     => 'Блог',
                        'versions' => [
                            ['version' => $config['version'], 'requires' => '', 'released_at' => '2020-01-02'],
                        ],
                    ],
                ],
            ]),
        ]);

        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        $response->assertSee('data-released="2020-01-02"', false);
        $response->assertSee('02.01.2020');
    }

    public function testIndexIgnoresRegistryDateOfOtherVersion(): void
    {
        ModuleRegistry::query()->truncate();

        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module' => 'Blog',
                        'name'   => 'Блог',
                        // Версия, которой на диске нет: её дата к карточке не относится
                        'versions' => [
                            ['version' => '999.0.0', 'requires' => '', 'released_at' => '2020-01-02'],
                        ],
                    ],
                ],
            ]),
        ]);

        ModuleRegistry::query()->create(['url' => 'https://registry.example.com/modules.json', 'active' => true]);

        $response = $this->actingAs($this->boss)->get(route('admin.modules.index'));

        $response->assertOk();
        $response->assertDontSee('data-released="2020-01-02"', false);
        $response->assertSee('data-released="' . date('Y-m-d', (int) filemtime(base_path('modules/Blog/module.php'))) . '"', false);
    }

    public function testMarketplaceShowsReleaseDate(): void
    {
        ModuleRegistry::query()->truncate();

        Http::fake([
            '*' => Http::response([
                'name'    => 'Test Registry',
                'modules' => [
                    [
                        'module'   => 'Nonexistent',
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
