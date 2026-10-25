<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AjaxResolveImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Незафейканный запрос — ошибка теста, а не поход в сеть
        Http::preventStrayRequests();
    }

    public function testLocalFilesAreNotRead(): void
    {
        // file:// проходил FILTER_VALIDATE_URL: любой пользователь читал файлы сервера,
        // а file:///dev/zero забивал память процесса
        $path = tempnam(sys_get_temp_dir(), 'og');
        file_put_contents($path, '<meta property="og:image" content="https://example.com/local.png">');

        try {
            $this->actingAs(User::factory()->create())
                ->getJson('/ajax/resolve-image?url=' . rawurlencode('file://' . $path))
                ->assertOk()
                ->assertExactJson(['image' => null]);
        } finally {
            unlink($path);
        }
    }

    public function testArrayUrlIsIgnored(): void
    {
        // Массив вместо строки в адресе раньше ронял страницу с 500
        $this->actingAs(User::factory()->create())
            ->getJson('/ajax/resolve-image?url[]=https://example.com')
            ->assertOk()
            ->assertExactJson(['image' => null]);
    }

    public function testGuestIsRejected(): void
    {
        $this->getJson('/ajax/resolve-image?url=' . rawurlencode('https://example.com'))
            ->assertStatus(403);
    }

    public function testImageIsFoundOnPublicAddress(): void
    {
        Http::fake(['93.184.216.34/*' => Http::response('<meta property="og:image" content="https://example.com/a.png">')]);

        $this->actingAs(User::factory()->create())
            ->getJson('/ajax/resolve-image?url=' . rawurlencode('http://93.184.216.34/page'))
            ->assertOk()
            ->assertExactJson(['image' => 'https://example.com/a.png']);
    }

    public function testInternalAddressesAreNotRequested(): void
    {
        Http::fake();
        $user = User::factory()->create();

        // Сервер не ходит по запросу пользователя в свою сеть и к метаданным облака
        foreach (['http://127.0.0.1/', 'http://169.254.169.254/latest/meta-data/', 'http://10.0.0.1/', 'http://[::1]/', 'http://localhost/', 'http://93.184.216.34:22/'] as $url) {
            $this->actingAs($user)
                ->getJson('/ajax/resolve-image?url=' . rawurlencode($url))
                ->assertOk()
                ->assertExactJson(['image' => null]);
        }

        Http::assertNothingSent();
    }

    public function testRedirectToInternalAddressIsNotFollowed(): void
    {
        Http::fake([
            '93.184.216.34/*' => Http::response('', 302, ['Location' => 'http://127.0.0.1/admin']),
            '127.0.0.1/*'     => Http::response('<meta property="og:image" content="https://example.com/inner.png">'),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/ajax/resolve-image?url=' . rawurlencode('http://93.184.216.34/go'))
            ->assertOk()
            ->assertExactJson(['image' => null]);

        Http::assertNotSent(static fn (Request $request) => str_contains($request->url(), '127.0.0.1'));
    }

    public function testRedirectToPublicAddressIsFollowed(): void
    {
        Http::fake([
            '93.184.216.34/*' => Http::response('', 301, ['Location' => 'https://93.184.216.35/page']),
            '93.184.216.35/*' => Http::response('<meta property="og:image" content="https://example.com/b.png">'),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/ajax/resolve-image?url=' . rawurlencode('http://93.184.216.34/page'))
            ->assertOk()
            ->assertExactJson(['image' => 'https://example.com/b.png']);
    }

    public function testRequestsAreThrottled(): void
    {
        $user = User::factory()->create();

        // Каждый вызов — запрос с сервера наружу: поток запросов не превращает сайт в прокси
        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($user)->getJson('/ajax/resolve-image?url=' . rawurlencode('http://127.0.0.1/'))->assertOk();
        }

        $this->actingAs($user)
            ->getJson('/ajax/resolve-image?url=' . rawurlencode('http://127.0.0.1/'))
            ->assertStatus(429);
    }
}
