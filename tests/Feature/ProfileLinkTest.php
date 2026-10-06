<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileLinkTest extends TestCase
{
    use RefreshDatabase;

    public function testClosedSectionIsPlainTextForGuest(): void
    {
        // Страницы раздела закрыты для гостей — ссылки в анкете вели бы на 403
        $html = $this->render(['guests' => false]);

        $this->assertStringNotContainsString('href=', $html);
        $this->assertStringContainsString('is-static', $html);
        $this->assertStringContainsString('12', $html);
    }

    public function testClosedSectionIsLinkForUser(): void
    {
        $this->actingAs(User::factory()->create());

        $html = $this->render(['guests' => false]);

        $this->assertStringContainsString('href="/section/user"', $html);
        $this->assertStringContainsString('href="/section/user/extra"', $html);
    }

    public function testOpenSectionIsLinkForGuest(): void
    {
        // По умолчанию карточка — ссылка: модули с открытыми страницами ничего не передают
        $this->assertStringContainsString('href="/section/user"', $this->render());
    }

    /**
     * @param array<string, mixed> $props
     */
    private function render(array $props = []): string
    {
        return view('components.profile.link', $props + [
            'icon'  => 'fas fa-star',
            'label' => 'Раздел',
            'url'   => '/section/user',
            'count' => 12,
            'extra' => ['label' => 'Ещё', 'url' => '/section/user/extra', 'count' => 3],
        ])->render();
    }
}
