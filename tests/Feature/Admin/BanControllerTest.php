<?php

namespace Tests\Feature\Admin;

use App\Models\Banhist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_ban']);
    }

    private function makeBannedUser(string $login = 'banned_user'): User
    {
        $user = User::factory()->create([
            'login'   => $login,
            'level'   => User::BANNED,
            'timeban' => now()->addDays(3),
        ]);

        Banhist::query()->create([
            'user_id'      => $user->id,
            'send_user_id' => $this->admin->id,
            'type'         => Banhist::BAN,
            'reason'       => 'Причина исходного бана',
            'term'         => 259200,
            'created_at'   => now(),
        ]);

        return $user;
    }

    public function testEditFormIsShown(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($this->admin)
            ->get('/admin/bans/edit?user=' . $user->login)
            ->assertOk();
    }

    public function testEditMissingUserReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/bans/edit?user=nobody')
            ->assertNotFound();
    }

    public function testBanUser(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/edit?user=' . $user->login, [
                'time'   => 3,
                'type'   => 'days',
                'reason' => 'Реклама на сайте',
            ]);

        $response->assertRedirect('admin/bans/edit?user=' . $user->login);

        $user->refresh();

        $this->assertSame(User::BANNED, $user->level);
        $this->assertTrue($user->timeban->isFuture());

        $this->assertDatabaseHas('banhist', [
            'user_id'      => $user->id,
            'send_user_id' => $this->admin->id,
            'type'         => Banhist::BAN,
            'term'         => 3 * 86400,
        ]);

        $response->assertSessionHas('success');
    }

    public function testBanUserConvertsHoursToSeconds(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($this->admin)
            ->post('/admin/bans/edit?user=' . $user->login, [
                'time'   => 5,
                'type'   => 'hours',
                'reason' => 'Реклама на сайте',
            ]);

        $this->assertDatabaseHas('banhist', [
            'user_id' => $user->id,
            'term'    => 5 * 3600,
        ]);
    }

    public function testBanUserConvertsMinutesToSeconds(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($this->admin)
            ->post('/admin/bans/edit?user=' . $user->login, [
                'time'   => 15,
                'type'   => 'minutes',
                'reason' => 'Реклама на сайте',
            ]);

        $this->assertDatabaseHas('banhist', [
            'user_id' => $user->id,
            'term'    => 15 * 60,
        ]);
    }

    public function testBanAlreadyBannedUserFails(): void
    {
        $user = $this->makeBannedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/edit?user=' . $user->login, [
                'time'   => 3,
                'type'   => 'days',
                'reason' => 'Повторная причина бана',
            ]);

        $this->assertDatabaseCount('banhist', 1);

        $response->assertSessionHasErrors();
    }

    public function testBanWithShortReasonFails(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/edit?user=' . $user->login, [
                'time'   => 3,
                'type'   => 'days',
                'reason' => 'ab',
            ]);

        $user->refresh();

        $this->assertSame(User::USER, $user->level);
        $this->assertDatabaseCount('banhist', 0);

        $response->assertSessionHasErrors('reason');
    }

    public function testBanShowsErrorOnFormAfterRedirect(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);
        $url = '/admin/bans/edit?user=' . $user->login;

        $response = $this->actingAs($this->admin)
            ->from($url)
            ->post($url, [
                'time'   => 7,
                'type'   => 'days',
                'reason' => 'ab',
            ]);

        $response->assertRedirect($url);

        $this->actingAs($this->admin)
            ->get($url)
            ->assertOk()
            ->assertSee('is-invalid')
            ->assertSee('value="7"', false);
    }

    public function testChangeBan(): void
    {
        $user = $this->makeBannedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/change?user=' . $user->login, [
                'timeban' => now()->addDays(10)->format('Y-m-d\TH:i'),
                'reason'  => 'Уточнённая причина бана',
            ]);

        $response->assertRedirect('admin/bans/edit?user=' . $user->login);

        $user->refresh();

        $this->assertTrue($user->timeban->isFuture());
        $this->assertSame(User::BANNED, $user->level);

        $this->assertDatabaseHas('banhist', [
            'user_id' => $user->id,
            'type'    => Banhist::CHANGE,
        ]);

        $response->assertSessionHas('success');
    }

    public function testChangeBanWithPastDateFails(): void
    {
        $user = $this->makeBannedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/change?user=' . $user->login, [
                'timeban' => now()->subDay()->format('Y-m-d\TH:i'),
                'reason'  => 'Уточнённая причина бана',
            ]);

        $this->assertDatabaseMissing('banhist', [
            'user_id' => $user->id,
            'type'    => Banhist::CHANGE,
        ]);

        $response->assertSessionHasErrors('timeban');
    }

    public function testChangeBanForNotBannedUserIsRejected(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($this->admin)
            ->get('/admin/bans/change?user=' . $user->login)
            ->assertOk()
            ->assertSee(__('admin.bans.user_not_banned'));
    }

    public function testUnbanUser(): void
    {
        $user = $this->makeBannedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/bans/unban', ['user' => $user->login]);

        $response->assertRedirect('admin/bans/edit?user=' . $user->login);

        $user->refresh();

        $this->assertSame(User::USER, $user->level);
        $this->assertNull($user->timeban);

        $this->assertDatabaseHas('banhist', [
            'user_id' => $user->id,
            'type'    => Banhist::UNBAN,
        ]);

        $response->assertSessionHas('success');
    }

    public function testUnbanNotBannedUserIsRejected(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($this->admin)
            ->post('/admin/bans/unban', ['user' => $user->login])
            ->assertOk()
            ->assertSee(__('admin.bans.user_not_banned'));
    }
}
