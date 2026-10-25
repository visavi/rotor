<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNameTest extends TestCase
{
    use RefreshDatabase;

    public function testDirectionControlsAreStrippedOnSave(): void
    {
        // «\u{202E}nimdA» на экране выглядит как «Admin»
        $user = User::factory()->create(['name' => "\u{202E}nimdA", 'status' => "Мо\u{2066}дер\u{2069}"]);

        $this->assertSame('nimdA', $user->refresh()->name);
        $this->assertSame('Модер', $user->refresh()->status);

        $user->update(['name' => null, 'status' => null]);

        $this->assertNull($user->refresh()->getAttribute('name'));
        $this->assertNull($user->refresh()->status);
    }

    public function testProfileEscapesName(): void
    {
        $user = User::factory()->create(['name' => '<b>Вася</b>']);

        $html = $user->getProfile()->toHtml();

        $this->assertStringContainsString('&lt;b&gt;Вася&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function testDefaultAvatarEscapesLetter(): void
    {
        // Буква имени шла в разметку как есть: имя «<» ломало аватарку
        $user = User::factory()->create(['name' => '<b>', 'avatar' => null]);

        $html = $user->getAvatar()->toHtml();

        $this->assertStringContainsString('>&lt;</span>', $html);
        $this->assertStringNotContainsString('><</span>', $html);
    }

    public function testOwnStatusUsesThemeClass(): void
    {
        // Свой статус красился инлайном, а не классом темы, как и остальные красные метки
        $user = User::factory()->create(['point' => 10, 'status' => 'Модер']);

        $this->assertSame('<span class="text-danger">Модер</span>', $user->getStatuses(0)[$user->id]);
    }
}
