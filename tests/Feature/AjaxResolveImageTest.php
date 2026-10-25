<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxResolveImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
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
}
