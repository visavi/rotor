<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyBonusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testBonusIsGrantedOnFirstVisit(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'money'     => 1000,
            'timebonus' => null,
        ]);

        $this->actingAs($user)->get('/')->assertOk();

        $user->refresh();

        $this->assertSame(1000 + (int) setting('bonusmoney'), (int) $user->money);
        $this->assertNotNull($user->timebonus);
    }

    public function testBonusMessageIsShown(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'money'     => 1000,
            'timebonus' => null,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertSee(__('main.daily_bonus', [
            'money' => plural(setting('bonusmoney'), setting('moneyname')),
        ]));
    }

    public function testBonusMessageIsShownOnce(): void
    {
        $user = User::factory()->create(['timebonus' => null]);
        $message = __('main.daily_bonus', ['money' => plural(setting('bonusmoney'), setting('moneyname'))]);

        $this->actingAs($user)->get('/')->assertSee($message);

        // Уведомление уже показано на первой странице: следующая его не повторяет
        $this->actingAs($user)->get('/users')->assertDontSee($message);
    }

    public function testBonusMessageAfterRedirectIsShownOnce(): void
    {
        $user = User::factory()->create(['email' => 'bonus@example.com', 'timebonus' => null]);
        $message = __('main.daily_bonus', ['money' => plural(setting('bonusmoney'), setting('moneyname'))]);

        // Первый запрос — отправка формы: бонус ждёт страницу, куда ведёт редирект
        $this->actingAs($user)->post('/accounts/apikey', ['action' => 'create']);

        $this->actingAs($user)->get('/settings')->assertSee($message);
        $this->actingAs($user)->get('/users')->assertDontSee($message);
    }

    public function testAjaxDoesNotTakeBonus(): void
    {
        $user = User::factory()->create(['money' => 1000, 'timebonus' => null]);

        // JSON уведомление не покажет: бонус ждёт открытия страницы
        $this->actingAs($user)->get('/users', ['X-Requested-With' => 'XMLHttpRequest']);

        $this->assertSame(1000, (int) $user->fresh()->money);
    }

    public function testBonusIsNotGrantedTwiceWithin23Hours(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'money'     => 1000,
            'timebonus' => now()->subHours(2),
        ]);

        $this->actingAs($user)->get('/');

        $user->refresh();
        $this->assertSame(1000, (int) $user->money);
    }

    public function testBonusIsGrantedAgainAfter23Hours(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'money'     => 1000,
            'timebonus' => now()->subHours(24),
        ]);

        $this->actingAs($user)->get('/');

        $user->refresh();
        $this->assertSame(1000 + (int) setting('bonusmoney'), (int) $user->money);
    }

    public function testBonusMessageSurvivesAlongsideControllerFlash(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'email'     => 'bonus@example.com',
            'money'     => 1000,
            'timebonus' => null,
        ]);

        // POST, где контроллер сам кладёт success через redirect()->with()
        $this->actingAs($user)->post('/accounts/apikey', ['action' => 'create']);

        // Отправка формы бонус не начисляет: уведомление некому показать
        $this->assertSame(1000, (int) $user->fresh()->money);

        // Бонус начисляется на странице, куда ведёт редирект, — рядом с сообщением контроллера
        $this->actingAs($user)
            ->get('/settings')
            ->assertSee(__('users.token_success_created'))
            ->assertSee(__('main.daily_bonus', [
                'money' => plural(setting('bonusmoney'), setting('moneyname')),
            ]));
    }
}
