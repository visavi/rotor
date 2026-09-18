<?php

namespace Tests\Feature;

use App\Jobs\SendMailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

        Queue::fake();

        $this->author = User::factory()->create();
    }

    public function testNotifiesUserWithUnreadMessages(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Первое');
        $user->sendMessage($this->author, 'Второе');
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $data = $this->queuedFor($user);

        $this->assertNotNull($data);
        // Число берётся withCount и должно совпадать с непрочитанными
        $this->assertStringContainsString('2 непрочитанных сообщений', $data['subject']);
        $this->assertStringContainsString('(2 шт.)', $data['text']);
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

        $this->assertStringContainsString('(1 шт.)', $this->queuedFor($one)['text']);
        $this->assertStringContainsString('(3 шт.)', $this->queuedFor($two)['text']);
    }

    public function testReadMessagesAreNotCounted(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Прочитанное');
        $user->sendMessage($this->author, 'Непрочитанное');
        $user->dialogues()->limit(1)->update(['reading' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertStringContainsString('(1 шт.)', $this->queuedFor($user)['text']);
    }

    public function testSkipsUserWithoutUnreadMessages(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Прочитанное');
        $user->dialogues()->update(['reading' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertNull($this->queuedFor($user));
        $this->assertSame(0, (int) $user->fresh()->sendprivatmail);
    }

    public function testSkipsRecentlyActiveUser(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');

        // updated_at свежее sendprivatmailday — пользователь заходил, письмо ни к чему
        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertNull($this->queuedFor($user));
    }

    public function testSkipsUserWithoutSubscription(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');
        User::query()->whereKey($user->id)->update(['subscribe' => null]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertNull($this->queuedFor($user));
    }

    public function testSkipsAlreadyNotifiedUser(): void
    {
        $user = $this->subscriber();
        $user->sendMessage($this->author, 'Письмо');
        User::query()->whereKey($user->id)->update(['sendprivatmail' => 1]);
        $this->makeStale($user);

        $this->artisan('add:subscribers')->assertSuccessful();

        $this->assertNull($this->queuedFor($user));
    }

    /**
     * Данные письма, поставленного в очередь для пользователя
     *
     * @return array<string, mixed>|null
     */
    private function queuedFor(User $user): ?array
    {
        $found = null;

        Queue::pushed(SendMailJob::class, static function (SendMailJob $job) use ($user, &$found) {
            if ($job->data['to'] === $user->email) {
                $found = $job->data;
            }

            return false;
        });

        return $found;
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
