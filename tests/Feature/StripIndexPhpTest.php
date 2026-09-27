<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripIndexPhpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testRouteBehindScriptRedirectsToCleanUrl(): void
    {
        $this->request('GET', '/index.php/forums')
            ->assertStatus(301)
            ->assertHeader('Location', '/forums');
    }

    public function testBareScriptRedirectsToHome(): void
    {
        $this->request('GET', '/index.php')
            ->assertStatus(301)
            ->assertHeader('Location', '/');
    }

    public function testQueryStringIsKept(): void
    {
        $this->request('GET', '/index.php/forums?page=2&sort=date')
            ->assertStatus(301)
            ->assertHeader('Location', '/forums?page=2&sort=date');
    }

    public function testSubdirectoryInstallKeepsBasePath(): void
    {
        $this->request('GET', '/sub/index.php/forums', '/sub/index.php')
            ->assertStatus(301)
            ->assertHeader('Location', '/sub/forums');
    }

    public function testPostIsNotRedirected(): void
    {
        // 301 превратил бы POST в GET и потерял данные формы
        $response = $this->request('POST', '/index.php/login');

        $this->assertNotSame(301, $response->getStatusCode());
    }

    public function testCleanUrlIsNotRedirected(): void
    {
        $this->request('GET', '/')->assertOk();
    }

    /**
     * Запрос с именем скрипта, как его передаёт веб-сервер: без SCRIPT_NAME
     * тестовый запрос не отличить от чистого адреса
     */
    private function request(string $method, string $uri, string $script = '/index.php'): TestResponse
    {
        return $this->call($method, $uri, server: [
            'SCRIPT_NAME'     => $script,
            'SCRIPT_FILENAME' => public_path('index.php'),
            'PHP_SELF'        => $script,
        ]);
    }
}
