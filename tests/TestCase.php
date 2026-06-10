<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        clearCache('settings');
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
