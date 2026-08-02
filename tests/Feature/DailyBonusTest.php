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

    public function testBonusMessageIsFlashed(): void
    {
        $user = User::factory()->create([
            'login'     => 'bonus_user',
            'money'     => 1000,
            'timebonus' => null,
        ]);

        $response = $this->actingAs($user)->get('/');

        $this->assertNotEmpty((array) session('success'));

        // И реально отрендерилось на странице
        $response->assertSee(__('main.daily_bonus', [
            'money' => plural(setting('bonusmoney'), setting('moneyname')),
        ]));
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

        // Оба уведомления лежат в одном ключе success и не затирают друг друга
        $this->assertCount(2, (array) session('success'));

        // Оба видны на странице, куда ведёт редирект
        $this->actingAs($user)
            ->get('/accounts')
            ->assertSee(__('users.token_success_created'))
            ->assertSee(__('main.daily_bonus', [
                'money' => plural(setting('bonusmoney'), setting('moneyname')),
            ]));
    }
}
