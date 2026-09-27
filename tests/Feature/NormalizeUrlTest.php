<?php

namespace Tests\Feature;

use App\Http\Middleware\NormalizeUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NormalizeUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    /**
     * Адрес запроса, имя скрипта и ожидаемый Location
     */
    public static function redirectProvider(): array
    {
        return [
            'маршрут за скриптом'                 => ['/index.php/forums', '/index.php', '/forums'],
            'голый скрипт'                        => ['/index.php', '/index.php', '/'],
            'скрипт со слэшем'                    => ['/index.php/', '/index.php', '/'],
            'строка запроса'                      => ['/index.php/forums?page=2&sort=date', '/index.php', '/forums?page=2&sort=date'],
            'скрипт в подкаталоге'                => ['/sub/index.php/forums', '/sub/index.php', '/sub/forums'],
            'слэш в конце'                        => ['/forums/', '/index.php', '/forums'],
            'несколько слэшей'                    => ['/forums///', '/index.php', '/forums'],
            'слэш перед запросом'                 => ['/forums/?page=2', '/index.php', '/forums?page=2'],
            'скрипт и слэш сразу'                 => ['/index.php/forums/', '/index.php', '/forums'],
            'слэш в подкаталоге'                  => ['/sub/forums/', '/sub/index.php', '/sub/forums'],
            'чужой хост не уводит'                => ['//evil.com/', '/index.php', '/evil.com'],
            'закодированная точка'                => ['/index%2Ephp/forums', '/index.php', '/forums'],
            'закодированная буква'                => ['/%69ndex.php/forums', '/index.php', '/forums'],
            'закодированный скрипт в подкаталоге' => ['/sub/index%2Ephp/forums/', '/sub/index.php', '/sub/forums'],
            'слэш при раскладке с public'         => ['/forums/', '/public/index.php', '/forums'],
        ];
    }

    #[DataProvider('redirectProvider')]
    public function testRedirectsToNormalizedUrl(string $uri, string $script, string $location): void
    {
        $response = $this->handle($uri, $script);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame($location, $response->headers->get('Location'));
    }

    /**
     * Адрес запроса и имя скрипта
     */
    public static function passProvider(): array
    {
        return [
            'корень'         => ['/', '/index.php'],
            'чистый адрес'   => ['/forums', '/index.php'],
            'строка запроса' => ['/forums?page=2', '/index.php'],
            // Корень подкаталога — каталог, слэш у него дописывает сам сервер
            'подкаталог'           => ['/sub', '/sub/index.php'],
            'подкаталог со слэшем' => ['/sub/', '/sub/index.php'],
            'скрипт внутри пути'   => ['/forums/index.php', '/index.php'],
            // Без SCRIPT_NAME базовый адрес и имя скрипта оба пусты
            'без имени скрипта' => ['/forums', ''],
            // Раскладка «всё в public_html»: скрипт /public/index.php, а в адресе /index.php —
            // Symfony теряет путь, такой адрес закрывает правило в public/.htaccess
            'скрипт вне адреса'       => ['/index.php/forums', '/public/index.php'],
            'голый скрипт вне адреса' => ['/index.php', '/public/index.php'],
        ];
    }

    #[DataProvider('passProvider')]
    public function testCleanUrlPasses(string $uri, string $script): void
    {
        $this->assertSame('passed', $this->handle($uri, $script)->getContent());
    }

    public function testPostIsNotRedirected(): void
    {
        // 301 превратил бы POST в GET и потерял данные формы
        $response = $this->handle('/index.php/forums/', '/index.php', 'POST');

        $this->assertSame('passed', $response->getContent());
    }

    public function testRegisteredInGlobalStack(): void
    {
        $this->call('GET', '/index.php/forums', server: $this->server('/index.php'))
            ->assertStatus(301)
            ->assertHeader('Location', '/forums');
    }

    public function testCleanUrlOpensPage(): void
    {
        $this->call('GET', '/', server: $this->server('/index.php'))
            ->assertOk();
    }

    /**
     * Запрос через middleware напрямую: хелперы тестов срезают слэш в конце адреса
     */
    private function handle(string $uri, string $script, string $method = 'GET'): Response
    {
        $request = new Request(server: $this->server($script) + [
            'REQUEST_URI'    => $uri,
            'REQUEST_METHOD' => $method,
        ]);

        return (new NormalizeUrl())->handle($request, static fn () => response('passed'));
    }

    /**
     * Имя скрипта, как его передаёт веб-сервер
     */
    private function server(string $script): array
    {
        return [
            'SCRIPT_NAME'     => $script,
            'SCRIPT_FILENAME' => $script === '' ? '' : public_path('index.php'),
            'PHP_SELF'        => $script,
        ];
    }
}
