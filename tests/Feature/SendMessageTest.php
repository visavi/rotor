<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    protected function tearDown(): void
    {
        Registry::$onSendMessage = [];

        parent::tearDown();
    }

    public function testHookReceivesMessageAndRecipient(): void
    {
        $calls = [];

        Registry::onSendMessage(static function (Message $message, User $user) use (&$calls): void {
            $calls[] = ['message' => $message->id, 'user' => $user->id, 'unread' => $user->getCountNewMessages()];
        });

        $recipient = User::factory()->create();
        $author = User::factory()->create();

        $message = $recipient->sendMessage($author, 'Привет');

        $this->assertCount(1, $calls);
        $this->assertSame($message->id, $calls[0]['message']);
        $this->assertSame($recipient->id, $calls[0]['user']);

        // Счётчик уже увеличен к моменту вызова
        $this->assertSame(1, (int) $calls[0]['unread']);
    }

    public function testHookFiresForSystemMessage(): void
    {
        $calls = 0;

        Registry::onSendMessage(static function () use (&$calls): void {
            $calls++;
        });

        $recipient = User::factory()->create();
        $recipient->sendMessage(null, 'Системное уведомление');

        $this->assertSame(1, $calls);
    }

    public function testMessageIsSentWithoutHandlers(): void
    {
        $recipient = User::factory()->create();
        $author = User::factory()->create();

        $recipient->sendMessage($author, 'Без обработчиков');

        $this->assertSame(1, $recipient->fresh()->getCountNewMessages());
    }
}
