<?php

namespace Tests\Feature;

use App\Models\Dialogue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewMessagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->user = User::factory()->create();
        $this->author = User::factory()->create();
    }

    public function testCountsOnlyUnreadDialogues(): void
    {
        $this->user->sendMessage($this->author, 'Первое');
        $this->user->sendMessage($this->author, 'Второе');

        $this->assertSame(2, $this->user->getCountNewMessages());

        Dialogue::query()->where('user_id', $this->user->id)->limit(1)->update(['reading' => 1]);

        $this->assertSame(1, $this->user->getCountNewMessages());
    }

    public function testThemesRenderTheCount(): void
    {
        $this->user->sendMessage($this->author, 'Письмо');

        $themes = array_map('basename', glob(resource_path('views/themes/*'), GLOB_ONLYDIR));

        $this->assertNotEmpty($themes);

        foreach ($themes as $theme) {
            $this->user->update(['themes' => $theme]);

            $this->actingAs($this->user)
                ->get('/')
                ->assertOk()
                // Бейдж рисуется числом из getCountNewMessages(), а не пустотой
                ->assertSee('js-message-count">1<', false);
        }
    }

    public function testValueIsNotRemembered(): void
    {
        $this->assertSame(0, $this->user->getCountNewMessages());

        $this->user->sendMessage($this->author, 'Письмо');

        // Ничего не кешируется: тот же объект видит новое значение сразу
        $this->assertSame(1, $this->user->getCountNewMessages());
    }

    public function testReadingDialogueDropsCountInSameRequest(): void
    {
        $this->user->sendMessage($this->author, 'Письмо');

        $this->actingAs($this->user)
            ->get('/messages/talk/' . $this->author->login)
            ->assertOk();

        $this->assertSame(0, $this->user->getCountNewMessages());
    }

    public function testApiKeepsNewprivatField(): void
    {
        $this->user->update(['apikey' => Str::random(32)]);
        $this->user->sendMessage($this->author, 'Письмо');

        $this->getJson('/api/user', ['Authorization' => 'Bearer ' . $this->user->apikey])
            ->assertOk()
            ->assertJsonPath('data.newprivat', 1);
    }

    public function testNewMessageRearmsReminderMail(): void
    {
        $this->user->update(['sendprivatmail' => 1]);

        $this->user->sendMessage($this->author, 'Письмо');

        $this->assertSame(0, (int) $this->user->fresh()->sendprivatmail);
    }
}
