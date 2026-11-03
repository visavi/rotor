<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Message;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use RefreshDatabase;

    private array $apiConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->apiConfig = Registry::$apiConfig;
    }

    protected function tearDown(): void
    {
        // Реестр статический: секция demo иначе доживёт до конца прогона
        Registry::$apiConfig = $this->apiConfig;

        parent::tearDown();
    }

    public function testConfigReturnsTypeLists(): void
    {
        $response = $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonStructure([
                'site',
                'upload'  => ['max_files', 'max_file_size', 'extensions', 'media_extensions'],
                'account' => ['login_min', 'password_min', 'captcha_type', 'confirm_email'],
                'types'   => ['search', 'comment', 'rating', 'media', 'file'],
            ]);

        // Комментарии и личные сообщения принимают файлы в любой сборке
        $this->assertContains(Comment::$morphName, $response->json('types.file'));
        $this->assertContains(Message::$morphName, $response->json('types.file'));

        // Пользователи и комментарии участвуют в поиске без модулей
        $this->assertArrayHasKey(Comment::$morphName, $response->json('types.search'));
    }

    public function testModuleSectionIsAddedFromRegistry(): void
    {
        $this->overrideSetting('demo_text_min', 7);

        // Ядро не знает про настройки модуля — модуль объявляет их в module.php
        Registry::apiConfig('demo', ['text_min' => 'demo_text_min', 'limit' => 10]);

        $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonPath('demo.text_min', 7)
            ->assertJsonPath('demo.limit', 10);
    }

    public function testDocsDescribeEveryCoreField(): void
    {
        // Клиент узнаёт поля из документации: поле без описания для него не существует,
        // а описанное, но не приходящее — ломает разбор ответа
        $config = $this->getJson('/api/config')->assertOk()->json();
        $schema = $this->getJson('/api/openapi.json')
            ->assertOk()
            ->json('paths./config.get.responses.200.content.application/json.schema.properties');

        foreach (['site', 'upload', 'message', 'account'] as $section) {
            $actual = array_keys($config[$section]);
            $described = array_keys($schema[$section]['properties']);
            sort($actual);
            sort($described);

            $this->assertSame($actual, $described, 'Раздел ' . $section);
        }
    }

    public function testModuleSectionValueCanBeComputed(): void
    {
        // Адрес страницы — не настройка: строку ядро приняло бы за имя настройки и отдало null
        Registry::apiConfig('demo', [
            'url'    => static fn (): string => url('/demo'),
            'format' => 'date',
        ]);

        $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonPath('demo.url', url('/demo'))
            // Строка с именем PHP-функции остаётся именем настройки, а не вызывается
            ->assertJsonPath('demo.format', null);
    }

    public function testCoreConfigHasNoModuleSections(): void
    {
        // Без модулей в ответе не должно быть секций форума
        $response = $this->getJson('/api/config')->assertOk();

        $this->assertArrayNotHasKey('forum', $response->json());
    }
}
