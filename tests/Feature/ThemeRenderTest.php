<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Проверяет, что каждая установленная тема отдаёт страницы без ошибок.
 *
 * Ловит поломки вёрстки уровня «тема не собралась»: отсутствующий blade,
 * опечатку в @include, обращение к удалённому partial
 */
class ThemeRenderTest extends TestCase
{
    use RefreshDatabase;

    /** Страницы с разной вёрсткой: главная, список, поиск */
    private const array PAGES = [
        '/',
        '/pages',
        '/users',
        '/search',
    ];

    /** Формы, доступные только гостю */
    private const array GUEST_PAGES = [
        '/login',
        '/register',
    ];

    /** Страницы личного кабинета */
    private const array USER_PAGES = [
        '/profile',
        '/settings',
    ];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->user = User::factory()->create(['login' => 'theme_test']);
    }

    public function testEveryThemeRendersPagesForGuest(): void
    {
        $this->assertThemesRender('гость', [...self::PAGES, ...self::GUEST_PAGES]);
    }

    public function testEveryThemeRendersPagesForUser(): void
    {
        $this->actingAs($this->user);

        $this->assertThemesRender('пользователь', [...self::PAGES, ...self::USER_PAGES]);
    }

    private function assertThemesRender(string $role, array $pages): void
    {
        $themes = getAvailableThemes();

        $this->assertNotEmpty($themes, 'Не найдено ни одной темы');

        foreach ($themes as $theme) {
            $this->overrideSetting('themes', $theme);

            foreach ($pages as $page) {
                $response = $this->get($page);

                $this->assertContains(
                    $response->getStatusCode(),
                    [200, 302],
                    sprintf(
                        "Тема %s, страница %s, %s: код %d\n%s",
                        $theme,
                        $page,
                        $role,
                        $response->getStatusCode(),
                        mb_substr(strip_tags($response->getContent()), 0, 500),
                    )
                );
            }
        }
    }
}
