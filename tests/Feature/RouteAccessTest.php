<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class RouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    private const array SKIP_PREFIXES = [
        '_debugbar',
        '_ignition',
        'install/',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_test']);
        $this->user = User::factory()->create(['login' => 'user_test']);
    }

    public function testRoutesAsGuest(): void
    {
        $this->assertNoRoutes500('guest');
    }

    public function testRoutesAsUser(): void
    {
        $this->actingAs($this->user);
        $this->assertNoRoutes500('user');
    }

    public function testRoutesAsAdmin(): void
    {
        $this->actingAs($this->admin);
        $this->assertNoRoutes500('admin');
    }

    private function assertNoRoutes500(string $role): void
    {
        foreach ($this->getGetRoutes() as $uri) {
            $response = $this->get($uri);
            $this->assertNotEquals(
                500,
                $response->getStatusCode(),
                "Route GET $uri returned 500 as $role\n" . substr(strip_tags($response->getContent()), 0, 500)
            );
        }
    }

    private function getGetRoutes(): array
    {
        $uris = [];

        /** @var Route $route */
        foreach (RouteFacade::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = $route->uri();

            // Пропускаем маршруты модулей — у них свои тесты
            $action = $route->getAction('controller') ?? '';
            if (str_starts_with($action, 'Modules\\')) {
                continue;
            }

            foreach (self::SKIP_PREFIXES as $prefix) {
                if (str_starts_with($uri, $prefix)) {
                    continue 2;
                }
            }

            $uris[] = $this->substituteParams($uri);
        }

        return array_unique($uris);
    }

    private function substituteParams(string $uri): string
    {
        $uri = preg_replace('/\/\{[^}]+\?\}/', '', $uri);

        $login = $this->admin->login;

        $uri = preg_replace('/\{login\}/', $login, $uri);
        $uri = preg_replace('/\{lang\}/', 'ru', $uri);
        $uri = preg_replace('/\{letter\}/', 'a', $uri);
        $uri = preg_replace('/\{tag\}/', 'test', $uri);
        $uri = preg_replace('/\{slug\}/', 'test-slug', $uri);
        $uri = preg_replace('/\{token\}/', 'invalid-token', $uri);

        $uri = preg_replace('/\{[^}]+\}/', '1', $uri);

        return '/' . ltrim($uri, '/');
    }
}
