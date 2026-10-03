<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Route::get('/api/test-crash', static fn () => throw new RuntimeException('crash'));
    }

    public function testApiErrorIsJsonWithoutAcceptHeader(): void
    {
        // Клиенты api не всегда шлют Accept: application/json, а html-страница
        // ошибки в api не отрисуется — тема там не подключается
        $this->get('/api/test-crash')
            ->assertStatus(500)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }
}
