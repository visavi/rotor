<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testBannedUserIsRedirectedToBanPage(): void
    {
        $user = User::factory()->create(['level' => User::BANNED]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('ban?user=' . $user->login);
    }

    public function testBannedUserReachesAllowedPages(): void
    {
        $user = User::factory()->create(['level' => User::BANNED]);

        $this->actingAs($user)->get('/rules')->assertOk();
    }

    public function testPendedUserIsRedirectedToVerify(): void
    {
        // Статус pended запирает пользователя, только когда почта подтверждается
        $this->overrideSetting('email_mode', UserService::EMAIL_CONFIRM);

        $user = User::factory()->create(['level' => User::PENDED]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('verify', ['user' => $user->login]));
    }

    public function testPendedUserPassesWhenEmailIsNotConfirmed(): void
    {
        $this->overrideSetting('email_mode', UserService::EMAIL_OPTIONAL);

        $user = User::factory()->create(['level' => User::PENDED]);

        $this->actingAs($user)->get('/')->assertOk();
    }

    public function testActiveUserPassesThrough(): void
    {
        $user = User::factory()->create(['level' => User::USER]);

        $this->actingAs($user)->get('/')->assertOk();
    }

    public function testGuestPassesThrough(): void
    {
        $this->get('/')->assertOk();
    }

    public function testBannedUserGetsNoBonus(): void
    {
        $user = User::factory()->create([
            'level'     => User::BANNED,
            'money'     => 1000,
            'timebonus' => null,
        ]);

        $this->actingAs($user)->get('/');

        $this->assertSame(1000, (int) $user->fresh()->money);
    }
}
