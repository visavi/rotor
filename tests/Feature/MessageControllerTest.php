<?php

namespace Tests\Feature;

use App\Models\Dialogue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('captcha_type', 'disable');
        $this->overrideSetting('comment_text_min', 5);
        $this->overrideSetting('comment_text_max', 1000);
        $this->overrideSetting('floodstime', 0);

        $this->user = User::factory()->create(['login' => 'sender', 'point' => 1000, 'timebonus' => now()]);
        $this->recipient = User::factory()->create(['login' => 'recipient']);
    }

    public function testSendMessage(): void
    {
        $this->actingAs($this->user)
            ->post('/messages/send?user=recipient', ['msg' => '<p>Привет, как дела?</p>'])
            ->assertRedirect('messages/talk/recipient')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('dialogues', [
            'user_id'   => $this->recipient->id,
            'author_id' => $this->user->id,
        ]);
    }

    public function testSendCountsTextWithoutTags(): void
    {
        $this->actingAs($this->user)
            ->post('/messages/send?user=recipient', ['msg' => '<p><strong>Хай</strong></p>'])
            ->assertRedirect('messages/talk/recipient')
            ->assertSessionHasErrors(['msg' => __('validator.length_min', ['length' => 5])]);

        $this->assertDatabaseCount('messages', 0);
    }

    public function testSendToYourselfFails(): void
    {
        $this->actingAs($this->user)
            ->post('/messages/send?user=sender', ['msg' => 'Письмо самому себе'])
            ->assertSessionHasErrors(['msg' => __('messages.send_yourself')]);

        $this->assertDatabaseCount('messages', 0);
    }

    public function testSendToUnknownUserReturns404(): void
    {
        $this->actingAs($this->user)
            ->post('/messages/send?user=nobody', ['msg' => 'Есть кто-нибудь?'])
            ->assertNotFound();
    }

    public function testCaptchaIsRequiredWithLowActivity(): void
    {
        $this->overrideSetting('captcha_type', 'graphical');
        $this->user->update(['point' => 0]);

        $this->actingAs($this->user)
            ->post('/messages/send?user=recipient', ['msg' => 'Сообщение без капчи'])
            ->assertSessionHasErrors(['protect' => __('validator.captcha')]);

        $this->assertDatabaseCount('messages', 0);
    }

    public function testDeleteDialogue(): void
    {
        $this->user->sendMessage($this->recipient, 'Входящее сообщение');
        Dialogue::query()->where('user_id', $this->user->id)->update(['reading' => 1]);

        $this->actingAs($this->user)
            ->delete('/messages/delete/' . $this->recipient->id)
            ->assertRedirect('messages?page=1')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('dialogues', ['user_id' => $this->user->id, 'author_id' => $this->recipient->id]);
    }

    public function testDeleteWithUnreadMessagesFails(): void
    {
        $this->user->sendMessage($this->recipient, 'Непрочитанное сообщение');

        $this->actingAs($this->user)
            ->delete('/messages/delete/' . $this->recipient->id)
            ->assertSessionHasErrors();

        $this->assertSame(__('messages.unread_messages'), session('errors')->first());
        $this->assertTrue(Dialogue::query()->where('user_id', $this->user->id)->exists());
    }

    public function testDeleteEmptyDialogueFails(): void
    {
        $this->actingAs($this->user)
            ->delete('/messages/delete/' . $this->recipient->id)
            ->assertSessionHasErrors();

        $this->assertSame(__('messages.empty_dialogue'), session('errors')->first());
    }
}
