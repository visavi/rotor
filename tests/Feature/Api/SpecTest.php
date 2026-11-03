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
        // Разделы описывают сами модули, в ядре их путей нет
        $paths = $this->getJson('/api/openapi.json')->assertOk()->json('paths');

        $this->assertArrayNotHasKey('/forums', $paths);
        $this->assertArrayNotHasKey('/news', $paths);
    }

    public function testCoreConfigDescribesOnlyItsSections(): void
    {
        // Разделы модулей описывают сами модули: выключенный не должен висеть в доке
        $sections = $this->getJson('/api/openapi.json')
            ->assertOk()
            ->json('paths./config.get.responses.200.content.application/json.schema.properties');

        $this->assertSame(['site', 'upload', 'account', 'message', 'types'], array_keys($sections));
    }

    public function testCoreKeepsOnlyItsOwnSchemas(): void
    {
        $schemas = $this->getJson('/api/openapi.json')->assertOk()->json('components.schemas');

        $this->assertArrayHasKey('Author', $schemas);
        $this->assertArrayNotHasKey('News', $schemas);
    }
}
