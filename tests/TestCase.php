<?php

namespace Tests;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Сидер прогоняется один раз внутри migrate:fresh (RefreshDatabase), а не в
     * каждом тесте. Иначе truncate() в сидерах даёт неявный COMMIT, рвёт per-test
     * транзакцию и заставляет migrate:fresh повторяться на каждом тесте.
     */
    protected string $seeder = DatabaseSeeder::class;

    /**
     * Время старта теста — по нему в конце находятся загруженные файлы
     */
    private int $uploadedAfter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // flush() сбрасывает и кеш, и процессный memo — иначе настройки протекают между тестами
        Setting::flush();

        $this->uploadedAfter = time();
    }

    protected function tearDown(): void
    {
        $this->clearUploads();

        parent::tearDown();
    }

    /**
     * Удаляет файлы, залитые тестом
     *
     * Загрузки пишутся в public напрямую, поэтому Storage::fake их не перехватывает,
     * а базу откатывает RefreshDatabase — без уборки в uploads копятся пустые картинки
     */
    private function clearUploads(): void
    {
        foreach (File::glob(public_path('uploads/*/*')) as $path) {
            if (is_file($path) && filemtime($path) >= $this->uploadedAfter) {
                File::delete($path);
            }
        }
    }

    /**
     * Переопределяет настройку сайта; БД откатит RefreshDatabase, memo сбросит setUp
     */
    protected function overrideSetting(string $name, mixed $value): void
    {
        Setting::query()->updateOrCreate(['name' => $name], ['value' => $value]);
        Setting::flush();
    }

    /**
     * @throws ReflectionException
     */
    protected function callMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        return (new ReflectionClass($object))
            ->getMethod($methodName)
            ->invokeArgs($object, $parameters);
    }
}
