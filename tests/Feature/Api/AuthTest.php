<?php

namespace Tests\Feature\Api;

use App\Models\BlackList;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('openreg', 1);
        $this->overrideSetting('regkeys', 0);
        $this->overrideSetting('captcha_type', 'graphical');
    }

    public function testCaptchaChallengeIsIssued(): void
    {
        $response = $this->getJson('/api/captcha')
            ->assertOk()
            ->assertJsonPath('type', 'graphical')
            ->assertJsonStructure(['type', 'key', 'image', 'expires_in']);

        $this->assertStringStartsWith('data:image/png;base64,', $response->json('image'));
        $this->assertNotNull(Cache::get(CaptchaService::CACHE_PREFIX . $response->json('key')));
    }

    public function testRegisterCreatesUserAndReturnsToken(): void
    {
        $response = $this->postJson('/api/register', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('pending', false)
            ->assertJsonPath('user.login', 'newuser');

        $user = User::query()->where('login', 'newuser')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::USER, $user->level);
        $this->assertSame($user->apikey, $response->json('token'));
        // Приветственное уведомление в приват, как и на сайте
        $this->assertSame(1, $user->getCountMessages());
    }

    public function testRegisterMarksAccountPendingWithConfirmation(): void
    {
        $this->overrideSetting('regkeys', 1);

        $this->postJson('/api/register', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('pending', true);

        $this->assertSame(User::PENDED, User::query()->where('login', 'newuser')->value('level'));
    }

    public function testCaptchaIsRequired(): void
    {
        $payload = $this->payload();
        $payload['protect'] = 'wrong';

        $this->postJson('/api/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('protect');
    }

    public function testCaptchaKeyIsSingleUse(): void
    {
        $payload = $this->payload();

        $this->postJson('/api/register', $payload)->assertStatus(201);

        // Тот же ключ второй раз не проходит — иначе им регистрировали бы пачками
        $payload['login'] = 'seconduser';
        $payload['email'] = 'second@example.com';

        $this->postJson('/api/register', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('protect');
    }

    public function testRegisterIsClosedWhenDisabled(): void
    {
        $this->overrideSetting('openreg', 0);

        $this->postJson('/api/register', $this->payload())->assertStatus(403);
    }

    public function testBlacklistedLoginIsRejected(): void
    {
        BlackList::query()->create(['type' => 'login', 'value' => 'newuser', 'user_id' => 0, 'created_at' => now()]);

        $this->postJson('/api/register', $this->payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('login');
    }

    public function testRecoverySendsResetLink(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/recovery', ['user' => $user->login] + $this->captcha())
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
    }

    public function testRecoveryIsNotRepeatedWithinHour(): void
    {
        $user = User::factory()->create();

        PasswordReset::query()->create([
            'email'      => $user->email,
            'token'      => 'token',
            'created_at' => now(),
        ]);

        $this->postJson('/api/recovery', ['user' => $user->login] + $this->captcha())
            ->assertStatus(422)
            ->assertJsonValidationErrors('user');
    }

    public function testRecoveryForUnknownUser(): void
    {
        $this->postJson('/api/recovery', ['user' => 'nobody'] + $this->captcha())
            ->assertStatus(422)
            ->assertJsonValidationErrors('user');
    }

    /**
     * Разгаданная капча: ключ и ответ вместо картинки
     */
    private function captcha(): array
    {
        $key = 'captchakey';
        Cache::put(CaptchaService::CACHE_PREFIX . $key, 'abc12', CaptchaService::LIFETIME);

        return ['captcha_key' => $key, 'protect' => 'abc12'];
    }

    private function payload(): array
    {
        return [
            'login'     => 'newuser',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newuser@example.com',
            'gender'    => User::MALE,
        ] + $this->captcha();
    }
}
