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

        $this->assertSame('nimdA', $user->fresh()->name);
        $this->assertSame('Модер', $user->fresh()->status);

        $user->update(['name' => null, 'status' => null]);

        $this->assertNull($user->fresh()->name);
        $this->assertNull($user->fresh()->status);
    }

    public function testProfileEscapesName(): void
    {
        $user = User::factory()->create(['name' => '<b>Вася</b>']);

        $html = $user->getProfile()->toHtml();

        $this->assertStringContainsString('&lt;b&gt;Вася&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
