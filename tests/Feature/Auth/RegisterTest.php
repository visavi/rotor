<?php

namespace Tests\Feature\Auth;

use App\Models\BlackList;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    private const array VALID_INPUT = [
        'login'     => 'newuser',
        'password'  => 'secret123',
        'password2' => 'secret123',
        'email'     => 'newuser@example.com',
        'gender'    => User::MALE,
        'protect'   => 'abc12',
    ];

    /**
     * Капча graphical: input protect сверяется с session protect
     */
    private function register(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['protect' => 'abc12'])
            ->post('/register', array_merge(self::VALID_INPUT, $overrides));
    }

    public function testRegisterCreatesUser(): void
    {
        $response = $this->register();

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $user = User::query()->where('login', 'newuser')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::USER, $user->level);
        $this->assertSame('newuser@example.com', $user->email);
        $this->assertSame((int) setting('registermoney'), (int) $user->money);
        $this->assertSame(setting('language'), $user->language);
        $this->assertAuthenticatedAs($user);

        // Приветственное уведомление в приват
        $this->assertSame(1, $user->getCountMessages());
    }

    public function testRegisterDisabledByOpenreg(): void
    {
        $this->overrideSetting('openreg', 0);

        $this->register();

        $this->assertDatabaseMissing('users', ['login' => 'newuser']);
        $this->assertGuest();
    }

    public function testRegisterWithDuplicateLogin(): void
    {
        User::factory()->create(['login' => 'newuser']);

        $response = $this->register(['email' => 'other@example.com']);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        User::factory()->create(['email' => 'newuser@example.com']);

        $response = $this->register(['login' => 'otheruser']);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    #[DataProvider('invalidLoginProvider')]
    public function testRegisterWithInvalidLogin(string $login): void
    {
        $response = $this->register(['login' => $login]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public static function invalidLoginProvider(): array
    {
        return [
            'короткий'            => ['ab'],
            'только цифры'        => ['12345'],
            'больше двух дефисов' => ['a-b-c-d'],
            'дефис первым'        => ['-abc'],
            'кириллица'           => ['логин'],
        ];
    }

    #[DataProvider('blacklistProvider')]
    public function testRegisterBlacklisted(string $type, string $value): void
    {
        BlackList::query()->create(['type' => $type, 'value' => $value]);

        $response = $this->register();

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public static function blacklistProvider(): array
    {
        return [
            'login'  => ['login', 'newuser'],
            'email'  => ['email', 'newuser@example.com'],
            'domain' => ['domain', 'example.com'],
        ];
    }

    public function testCaptchaCodeIsSingleUse(): void
    {
        // Первая попытка потребляет код из сессии (невалидный логин — регистрации нет)
        $this->register(['login' => 'ab']);

        // Повтор с тем же кодом и валидными данными — капча уже потреблена
        $response = $this->post('/register', self::VALID_INPUT);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function testRegisterWithoutEmailWhenOptional(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_OPTIONAL);

        $response = $this->register(['email' => '']);

        $response->assertRedirect('/');

        $user = User::query()->where('login', 'newuser')->first();
        $this->assertNotNull($user);
        // Пустая строка сломала бы уникальный индекс на втором таком аккаунте
        $this->assertNull($user->email);
        $this->assertSame(User::USER, $user->level);
        $this->assertAuthenticatedAs($user);
    }

    public function testSecondRegisterWithoutEmailAllowed(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_OPTIONAL);

        User::factory()->create(['email' => null]);

        $this->register(['email' => '']);

        $this->assertDatabaseCount('users', 2);
        $this->assertNotNull(User::query()->where('login', 'newuser')->first());
    }

    public function testRegisterWithoutEmailRejectedWhenRequired(): void
    {
        $response = $this->register(['email' => '']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function testRegisterWithoutEmailRejectedWithConfirmation(): void
    {
        // Подтверждать регистрацию нечем: режим подтверждения требует адрес
        $this->overrideSetting('email_mode', UserService::EMAIL_CONFIRM);

        $response = $this->register(['email' => '']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function testRecoveryWithoutEmailRejected(): void
    {
        $user = User::factory()->create(['email' => null]);

        $response = $this->withSession(['protect' => 'abc12'])
            ->post('/recovery', ['user' => $user->login, 'protect' => 'abc12']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('user');
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function testHiddenEmailFieldIsNotShownAndInputIgnored(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_HIDDEN);

        $this->get('/register')->assertDontSee('name="email"', false);

        // Скрытое поле не значит «принимаем из запроса» — адрес легко подсунуть POST-ом
        $this->register(['email' => 'sneaky@example.com']);

        $user = User::query()->where('login', 'newuser')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email);
    }

    public function testRegisterWithConfirmationRequiresConfirmation(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_CONFIRM);

        $this->register();

        $user = User::query()->where('login', 'newuser')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::PENDED, $user->level);
        $this->assertNotNull($user->confirm_token);

        $response = $this->get('/confirm/' . $user->confirm_token);

        $response->assertRedirect('/');
        $user->refresh();
        $this->assertSame(User::USER, $user->level);
        $this->assertNull($user->confirm_token);
    }
}
