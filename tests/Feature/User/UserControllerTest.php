<?php

namespace Tests\Feature\User;

use App\Models\BlackList;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        // captchaVerify() возвращает true только при отключённой капче
        $this->overrideSetting('captcha_type', 'disable');

        Mail::fake();
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'login'     => 'plain_user',
            'email'     => 'plain@example.com',
            'password'  => Hash::make('secret123'),
            'timebonus' => now(),
        ], $attributes));
    }

    public function testProfilePageIsShown(): void
    {
        $user = $this->makeUser();

        $this->get('/users/' . $user->login)
            ->assertOk()
            ->assertSee($user->login);
    }

    public function testProfilePageOfMissingUserReturns404(): void
    {
        $this->get('/users/nobody')->assertNotFound();
    }

    public function testRegister(): void
    {
        $response = $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newcomer@example.com',
            'gender'    => User::MALE,
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'login' => 'newcomer',
            'email' => 'newcomer@example.com',
            'level' => User::USER,
        ]);

        $response->assertSessionHas('success');
    }

    public function testRegisterWithExistingLoginFails(): void
    {
        $this->makeUser(['login' => 'newcomer', 'email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newcomer@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'newcomer@example.com']);

        $response->assertSessionHasErrors('login');
    }

    public function testRegisterWithMismatchedPasswordsFails(): void
    {
        $response = $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'other-password',
            'email'     => 'newcomer@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['login' => 'newcomer']);

        $response->assertSessionHasErrors('password2');
    }

    public function testRegisterWithBlacklistedLoginFails(): void
    {
        $admin = $this->makeUser(['login' => 'admin_blacklist', 'email' => 'a@example.com']);

        BlackList::query()->create([
            'type'       => 'login',
            'value'      => 'newcomer',
            'user_id'    => $admin->id,
            'created_at' => now(),
        ]);

        $response = $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newcomer@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['login' => 'newcomer']);

        $response->assertSessionHasErrors('login');
    }

    public function testRegisterKeepsInputOnError(): void
    {
        $response = $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'other-password',
            'email'     => 'newcomer@example.com',
        ]);

        $response->assertSessionHasInput('login', 'newcomer');
        $response->assertSessionHasInput('email', 'newcomer@example.com');
    }

    public function testLogin(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', [
            'login'    => $user->login,
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertSessionHas('success');
    }

    public function testLoginByEmail(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'login'    => $user->email,
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', [
            'login'    => $user->login,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('login');
        $response->assertSessionHas('danger');
    }

    public function testLoginKeepsInputOnError(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', [
            'login'    => $user->login,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasInput('login', $user->login);
    }

    public function testEditProfile(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post('/profile', [
            'name'    => 'Новое имя',
            'country' => 'Россия',
            'city'    => 'Москва',
            'gender'  => User::MALE,
            'info'    => 'Информация о себе',
        ]);

        $response->assertRedirect('profile');

        $user->refresh();
        $this->assertSame('Новое имя', $user->name);
        $this->assertSame('Москва', $user->city);

        $response->assertSessionHas('success');
    }

    public function testEditProfileWithInvalidSiteFails(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post('/profile', [
            'name' => 'Новое имя',
            'site' => 'не-адрес',
        ]);

        $user->refresh();
        $this->assertNotSame('Новое имя', $user->name);

        $response->assertSessionHasErrors('site');
    }

    public function testEditProfileShowsErrorOnFormAfterRedirect(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)
            ->from('/profile')
            ->post('/profile', [
                'name' => 'Новое имя',
                'site' => 'не-адрес',
            ]);

        $response->assertRedirect('/profile');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('is-invalid');
    }

    public function testEditSettings(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post('/settings', [
            'themes'   => 'default',
            'timezone' => 3,
            'language' => setting('language'),
        ]);

        $response->assertRedirect('settings');

        $user->refresh();
        $this->assertSame(3, (int) $user->timezone);

        $response->assertSessionHas('success');
    }

    public function testEditSettingsWithUnknownThemeFails(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post('/settings', [
            'themes'   => 'nonexistent',
            'timezone' => 3,
            'language' => setting('language'),
        ]);

        $user->refresh();
        $this->assertNotSame('nonexistent', $user->themes);

        $response->assertSessionHasErrors('themes');
    }

    public function testConfirmActivatesAccount(): void
    {
        $user = $this->makeUser([
            'level'         => User::PENDED,
            'confirm_token' => 'confirmtokentest',
        ]);

        $response = $this->get('/confirm/confirmtokentest');

        $response->assertRedirect('/');

        $user->refresh();
        $this->assertSame(User::USER, $user->level);
        $this->assertNull($user->confirm_token);

        $response->assertSessionHas('success');
    }

    public function testVerifyResendsConfirmation(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_CONFIRM);

        $user = $this->makeUser(['level' => User::PENDED]);

        $response = $this->actingAs($user)->post('/verify', [
            'email' => 'confirmed@example.com',
        ]);

        $response->assertRedirect(route('verify'));

        $user->refresh();
        $this->assertSame('confirmed@example.com', $user->email);
        $this->assertNotNull($user->confirm_token);

        $response->assertSessionHas('success');
    }

    public function testVerifyWithExistingEmailFails(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_CONFIRM);

        $this->makeUser(['login' => 'other_user', 'email' => 'taken@example.com']);
        $user = $this->makeUser(['level' => User::PENDED]);

        $response = $this->actingAs($user)->post('/verify', [
            'email' => 'taken@example.com',
        ]);

        $user->refresh();
        $this->assertNotSame('taken@example.com', $user->email);

        $response->assertSessionHasErrors('email');
    }
}
