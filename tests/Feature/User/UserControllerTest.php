<?php

namespace Tests\Feature\User;

use App\Models\BlackList;
use App\Models\User;
use App\Services\UserService;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;
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

    public function testUserListIgnoresUnknownSort(): void
    {
        $this->makeUser();

        $this->get('/users?sort=created_at')
            ->assertOk()
            ->assertSee('plain_user');
    }

    public function testProfilePageIsShown(): void
    {
        $user = $this->makeUser();

        $this->get('/users/' . $user->login)
            ->assertOk()
            ->assertSee($user->login);
    }

    public function testProfileActionsAreHiddenFromGuest(): void
    {
        $user = $this->makeUser(['site' => '']);

        // Блок действий состоит из хуков и ссылок для авторизованных — гостю показывать нечего
        $this->get('/users/' . $user->login)
            ->assertOk()
            ->assertDontSee('profile-actions');
    }

    public function testAccountPagesAreForbiddenToGuest(): void
    {
        // Вход проверяет middleware check.user, а не запросы и контроллеры
        $routes = [
            ['get', '/profile'],
            ['post', '/profile'],
            ['get', '/settings'],
            ['post', '/settings'],
            ['post', '/accounts/changemail'],
            ['get', '/accounts/editmail/token'],
            ['post', '/accounts/editstatus'],
            ['post', '/accounts/editcolor'],
            ['post', '/accounts/editpassword'],
            ['post', '/accounts/apikey'],
        ];

        foreach ($routes as [$method, $uri]) {
            $this->$method($uri)->assertForbidden();
        }
    }

    public function testProfileActionsAreShownToOwner(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/users/' . $user->login)
            ->assertOk()
            ->assertSee('profile-actions');
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

    public function testRegisterReportsLoginFormat(): void
    {
        $this->post('/register', [
            'login'     => '-newcomer',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newcomer@example.com',
        ])->assertSessionHasErrors(['login' => __('users.login_begin_requirements')]);

        $this->assertDatabaseMissing('users', ['login' => '-newcomer']);
    }

    public function testRegisterWithBlacklistedDomainFails(): void
    {
        BlackList::factory()->create(['type' => 'domain', 'value' => 'spam.example', 'user_id' => 0]);

        $this->post('/register', [
            'login'     => 'newcomer',
            'password'  => 'secret123',
            'password2' => 'secret123',
            'email'     => 'newcomer@SPAM.example',
        ])->assertSessionHasErrors(['email' => __('users.domain_is_blacklisted')]);

        $this->assertDatabaseMissing('users', ['login' => 'newcomer']);
    }

    public function testRegisterModuleHookAddsError(): void
    {
        $handlers = Registry::$onRegisterValidate;

        // Хук получает валидатор Laravel и пишет ошибку в его список
        Registry::$onRegisterValidate = [static function (Request $request, Validator $validator): void {
            $validator->errors()->add('invite', 'Нужен код приглашения');
        }];

        try {
            $this->post('/register', [
                'login'     => 'newcomer',
                'password'  => 'secret123',
                'password2' => 'secret123',
                'email'     => 'newcomer@example.com',
            ])->assertSessionHasErrors(['invite' => 'Нужен код приглашения']);
        } finally {
            Registry::$onRegisterValidate = $handlers;
        }

        $this->assertDatabaseMissing('users', ['login' => 'newcomer']);
    }

    public function testCheckLoginReportsReason(): void
    {
        $this->makeUser();

        // ajax ядра сводит 422 к {success: false, message}
        $this->postJson('/check-login', ['login' => 'plain_user'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['login' => __('validator.login')]);

        $this->postJson('/check-login', ['login' => 'plain-user'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson(['success' => true]);

        $this->makeUser(['login' => 'taken-user', 'email' => 'taken@example.com']);

        $this->postJson('/check-login', ['login' => 'taken-user'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['login' => __('users.login_already_exists')]);
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

        BlackList::factory()->create([
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

    public function testLoginIgnoresForeignReferer(): void
    {
        $user = $this->makeUser();

        // Ссылка на вход с чужого сайта не должна уводить туда после входа
        $this->withHeader('Referer', 'https://evil.example/phishing')->get('/login')->assertOk();

        $this->post('/login', ['login' => $user->login, 'password' => 'secret123'])
            ->assertRedirect(url('/'));
    }

    public function testLoginReturnsToPreviousPage(): void
    {
        $user = $this->makeUser();

        $this->withHeader('Referer', url('/forums'))->get('/login');

        $this->post('/login', ['login' => $user->login, 'password' => 'secret123'])
            ->assertRedirect(url('/forums'));
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

    public function testProfileFieldsAreEscaped(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/profile', [
            'country' => '<b>Страна</b>',
            'city'    => '<img src=x onerror=alert(1)>',
            'gender'  => User::MALE,
        ])->assertRedirect('profile');

        // Город и страна — свободный ввод, в анкете они выводятся текстом, а не разметкой
        $this->get('/users/' . $user->login)
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('<b>Страна</b>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    }

    public function testEditProfileNormalizesPlace(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/profile', [
            'country' => '  россия ',
            'city'    => ' нижний   Новгород',
            'gender'  => User::MALE,
        ])->assertRedirect('profile');

        $user->refresh();
        $this->assertSame('Россия', $user->country);
        $this->assertSame('Нижний Новгород', $user->city);
    }

    public function testProfileFormSuggestsPlaces(): void
    {
        $this->actingAs($this->makeUser(['city' => 'Казань']))
            ->get('/profile')
            ->assertOk()
            ->assertSee(route('ajax.places', ['field' => 'country']), false)
            ->assertSee(route('ajax.places', ['field' => 'city']), false)
            ->assertSee('<option value="Казань" selected>Казань</option>', false);
    }

    public function testPlaceSuggestions(): void
    {
        $user = $this->makeUser(['city' => 'Москва']);
        User::factory()->create(['city' => 'Москва']);
        User::factory()->create(['city' => 'Мозырь']);
        User::factory()->create(['city' => 'Казань']);

        $this->actingAs($user)
            ->getJson(route('ajax.places', ['field' => 'city', 'query' => 'М']))
            ->assertOk()
            ->assertExactJson([]);

        // Частые первыми, регистр запроса не важен
        $this->actingAs($user)
            ->getJson(route('ajax.places', ['field' => 'city', 'query' => 'мо']))
            ->assertOk()
            ->assertExactJson([
                ['value' => 'Москва', 'label' => 'Москва'],
                ['value' => 'Мозырь', 'label' => 'Мозырь'],
            ]);

        // % в запросе ищется буквально, а не как шаблон
        $this->actingAs($user)
            ->getJson(route('ajax.places', ['field' => 'city', 'query' => 'М%']))
            ->assertOk()
            ->assertExactJson([]);

        // Подсказки только по городу и стране
        $this->actingAs($user)
            ->getJson('/ajax/places/login?query=pl')
            ->assertNotFound();
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

    public function testEditSettingsAcceptsEasternmostTimezone(): void
    {
        $user = $this->makeUser();

        // UTC+14 — Кирибати
        $this->actingAs($user)->post('/settings', [
            'themes'   => 'default',
            'timezone' => 14,
            'language' => setting('language'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(14, (int) $user->fresh()->timezone);
    }

    public function testSettingsFormOffersEasternmostTimezone(): void
    {
        // Список в форме совпадает с проверкой: от UTC−12 до UTC+14
        $this->actingAs($this->makeUser())
            ->get('/settings')
            ->assertOk()
            ->assertSee('<option value="-12"', false)
            ->assertSee('<option value="14"', false);
    }

    public function testEditSettingsWithTimezoneOutOfRangeFails(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/settings', [
            'themes'   => 'default',
            'timezone' => 99,
            'language' => setting('language'),
        ])->assertSessionHasErrors(['timezone' => __('users.timezone_invalid')]);
    }

    public function testEditSettingsModuleHookAddsError(): void
    {
        $user = $this->makeUser();
        $handlers = Registry::$onSettingsValidate;

        Registry::$onSettingsValidate = [static function (User $user, Request $request, Validator $validator): void {
            $validator->errors()->add('module_option', 'Ошибка модуля');
        }];

        try {
            $this->actingAs($user)->post('/settings', [
                'themes'   => 'default',
                'timezone' => 5,
                'language' => setting('language'),
            ])->assertSessionHasErrors(['module_option' => 'Ошибка модуля']);
        } finally {
            Registry::$onSettingsValidate = $handlers;
        }

        $this->assertNotSame(5, (int) $user->fresh()->timezone);
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

    public function testConfirmDoesNotDemoteActiveUser(): void
    {
        // Админ одобрил регистрацию и назначил модератором, а ссылка из письма осталась
        $user = $this->makeUser([
            'level'         => User::MODER,
            'confirm_token' => 'oldconfirmtoken',
        ]);

        $this->get('/confirm/oldconfirmtoken')->assertNotFound();

        $this->assertSame(User::MODER, $user->fresh()->level);
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
