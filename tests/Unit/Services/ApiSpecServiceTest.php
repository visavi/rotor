<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ApiSpecService;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ApiSpecService::class)]
class ApiSpecServiceTest extends TestCase
{
    public function testModuleFieldsAreAddedToCoreRequest(): void
    {
        $spec = ApiSpecService::mergeRequestFields($this->spec(), [
            '/register' => [
                'post' => [
                    'properties' => ['privacy' => ['type' => 'boolean']],
                    'required'   => ['privacy', 'login'],
                ],
            ],
        ]);

        $schema = $spec['paths']['/register']['post']['requestBody']['content']['application/json']['schema'];

        // Поля ядра остаются, поле модуля добавляется в конец
        $this->assertSame(['login', 'email', 'privacy'], array_keys($schema['properties']));
        $this->assertSame(['type' => 'boolean'], $schema['properties']['privacy']);
        // Списки обязательных полей объединяются без повторов, а не затираются по номерам
        $this->assertSame(['login', 'email', 'privacy'], $schema['required']);
    }

    public function testOptionalFieldDoesNotAddEmptyRequiredList(): void
    {
        $spec = ApiSpecService::mergeRequestFields($this->spec(), [
            '/auth' => ['post' => ['properties' => ['remember' => ['type' => 'boolean']]]],
        ]);

        $schema = $spec['paths']['/auth']['post']['requestBody']['content']['application/json']['schema'];

        // Пустой required в OpenAPI 3.0 недопустим — Swagger ругается на схему
        $this->assertArrayNotHasKey('required', $schema);
        $this->assertArrayHasKey('remember', $schema['properties']);
    }

    public function testModuleConfigSectionIsDescribed(): void
    {
        $spec = $this->spec();
        $spec['paths']['/config']['get']['responses']['200']['content']['application/json']['schema'] = [
            'type'       => 'object',
            'properties' => ['site' => ['type' => 'object']],
        ];

        $spec = ApiSpecService::mergeConfigSections($spec, [
            'forum' => ['type' => 'object', 'properties' => ['text_min' => ['type' => 'integer']]],
        ]);

        $properties = $spec['paths']['/config']['get']['responses']['200']['content']['application/json']['schema']['properties'];

        $this->assertSame(['site', 'forum'], array_keys($properties));
        $this->assertSame(['type' => 'integer'], $properties['forum']['properties']['text_min']);
    }

    public function testConfigSectionsWithoutConfigPathAreSkipped(): void
    {
        $spec = ApiSpecService::mergeConfigSections($this->spec(), [
            'forum' => ['type' => 'object'],
        ]);

        $this->assertSame($this->spec(), $spec);
    }

    public function testMissingPathIsNotCreated(): void
    {
        // Ядро старее модуля или адрес с опечаткой — пустой адрес в доке появиться не должен
        $spec = ApiSpecService::mergeRequestFields($this->spec(), [
            '/unknown'  => ['post' => ['properties' => ['field' => ['type' => 'string']]]],
            '/register' => ['get' => ['properties' => ['field' => ['type' => 'string']]]],
        ]);

        $this->assertSame($this->spec(), $spec);
    }

    public function testBrokenModuleBlockIsIgnored(): void
    {
        $spec = ApiSpecService::mergeRequestFields($this->spec(), [
            '/register' => 'строка вместо описания',
            '/auth'     => ['post' => 'строка вместо полей'],
        ]);

        $this->assertSame($this->spec(), $spec);
    }

    private function spec(): array
    {
        return [
            'paths' => [
                '/register' => [
                    'post' => [
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type'       => 'object',
                                        'required'   => ['login', 'email'],
                                        'properties' => [
                                            'login' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/auth' => [
                    'post' => [
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
