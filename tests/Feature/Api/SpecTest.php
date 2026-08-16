<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

class SpecTest extends TestCase
{
    public function testSpecContainsCoreMethods(): void
    {
        $response = $this->getJson('/api/openapi.json')->assertOk();

        $this->assertArrayHasKey('post', $response->json('paths./auth'));
        $this->assertArrayHasKey('get', $response->json('paths./config'));
    }

    public function testSectionsOfDisabledModulesAreHidden(): void
    {
        // Модулей в тестах нет, значит и разделов в доке быть не должно
        $paths = $this->getJson('/api/openapi.json')->assertOk()->json('paths');

        $this->assertArrayNotHasKey('/forums', $paths);
        $this->assertArrayNotHasKey('/news', $paths);
    }

    public function testUnusedSchemasAreRemoved(): void
    {
        $schemas = $this->getJson('/api/openapi.json')->assertOk()->json('components.schemas');

        $this->assertArrayHasKey('Author', $schemas);
        $this->assertArrayNotHasKey('News', $schemas);
    }
}
