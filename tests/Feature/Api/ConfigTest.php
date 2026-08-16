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

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testConfigReturnsTypeLists(): void
    {
        $response = $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonStructure([
                'site',
                'upload',
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

    public function testCoreConfigHasNoModuleSections(): void
    {
        // Без модулей в ответе не должно быть секций форума
        $response = $this->getJson('/api/config')->assertOk();

        $this->assertArrayNotHasKey('forum', $response->json());
    }
}
