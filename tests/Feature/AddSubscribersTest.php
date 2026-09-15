<?php

namespace Tests\Feature;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddSubscribersTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('sendprivatmailday', 3);

        $this->author = User::factory()->create();
    }

    public function testNotifiesUserWithUnreadMessages(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Первое');
        $user->sendMessage($this->author, 'Второе');
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $mailing = Mailing::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($mailing);
        $this->assertSame('messages', $mailing->type);
        // Число берётся withCount и должно совпадать с непрочитанными
        $this->assertStringContainsString('2 непрочитанных сообщений', $mailing->subject);
        $this->assertStringContainsString('(2 шт.)', $mailing->text);
        $this->assertSame(1, (int) $user->fresh()->sendprivatmail);
    }

    public function testCountIsPerUser(): void
    {
        $one = $this->subscriber();
        $one->sendMessage($this->author, 'Одно');

        $two = $this->subscriber();
        $two->sendMessage($this->author, 'Раз');
        $two->sendMessage($this->author, 'Два');
        $two->sendMessage($this->author, 'Три');

        $this->makeStale($one);
        $this->makeStale($two);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertStringContainsString('(1 шт.)', Mailing::query()->where('user_id', $one->id)->value('text'));
        $this->assertStringContainsString('(3 шт.)', Mailing::query()->where('user_id', $two->id)->value('text'));
    }

    public function testReadMessagesAreNotCounted(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Прочитанное');
        $user->sendMessage($this->author, 'Непрочитанное');
        $user->dialogues()->limit(1)->update(['reading' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertStringContainsString('(1 шт.)', Mailing::query()->where('user_id', $user->id)->value('text'));
    }

    public function testSkipsUserWithoutUnreadMessages(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Прочитанное');
        $user->dialogues()->update(['reading' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertDatabaseMissing('mailings', ['user_id' => $user->id]);
        $this->assertSame(0, (int) $user->fresh()->sendprivatmail);
    }

    public function testSkipsRecentlyActiveUser(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');

        // updated_at свежее sendprivatmailday — пользователь заходил, письмо ни к чему
        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertDatabaseMissing('mailings', ['user_id' => $user->id]);
    }

    public function testSkipsUserWithoutSubscription(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');
        User::query()->whereKey($user->id)->update(['subscribe' => null]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertDatabaseMissing('mailings', ['user_id' => $user->id]);
    }

    public function testSkipsAlreadyNotifiedUser(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');
        User::query()->whereKey($user->id)->update(['sendprivatmail' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertDatabaseMissing('mailings', ['user_id' => $user->id]);
    }

    /**
     * Пользователь, подходящий под рассылку всем, кроме давности визита
     */
    private function subscriber(): User
    {
        return User::factory()->create([
            'subscribe'      => Str::random(32),
            'sendprivatmail' => 0,
        ]);
    }

    /**
     * Отодвигает последнюю активность за границу sendprivatmailday
     */
    private function makeStale(User $user): void
    {
        User::query()->whereKey($user->id)->update(['updated_at' => now()->subDays(5)]);
    }
}
