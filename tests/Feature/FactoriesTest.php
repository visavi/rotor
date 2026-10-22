<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Каждая фабрика ядра и модулей создаёт запись в строгом MySQL:
     * новая NOT NULL-колонка без default, забытая в фабрике, ловится здесь
     */
    public function testEveryFactoryCreatesRecord(): void
    {
        $factories = [
            ...glob(database_path('factories/*Factory.php')),
            ...glob(base_path('modules/*/Tests/Factories/*Factory.php')),
        ];

        $this->assertNotEmpty($factories);

        $factories = array_map(fn (string $path): Factory => $this->factoryClass($path)::new(), $factories);

        // Модели модулей попадают в карту morph при подключении модуля, здесь его нет.
        // Карта нужна заранее: фабрика создаёт и связанные записи других моделей
        foreach ($factories as $factory) {
            $model = $factory->modelName();

            if (property_exists($model, 'morphName')) {
                Relation::morphMap([$model::$morphName => $model]);
            }
        }

        foreach ($factories as $factory) {
            $this->assertTrue($factory->create()->exists, $factory::class);
        }
    }

    private function factoryClass(string $path): string
    {
        $name = basename($path, '.php');

        if (str_starts_with($path, database_path())) {
            return 'Database\\Factories\\' . $name;
        }

        $module = Str::between($path, base_path('modules/'), '/Tests/');

        return 'Modules\\' . $module . '\\Tests\\Factories\\' . $name;
    }
}
