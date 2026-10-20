<?php

namespace Tests\Feature\User;

use App\Models\Banhist;
use App\Models\Dialogue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private Banhist $banhist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('addbansend', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'ban_admin']);
        $this->user = User::factory()->create([
            'login'   => 'banned_user',
            'level'   => User::BANNED,
            'timeban' => now()->addDay(),
        ]);

        $this->banhist = Banhist::query()->create([
            'user_id'      => $this->user->id,
            'send_user_id' => $this->admin->id,
            'type'         => Banhist::BAN,
            'reason'       => 'Причина бана',
            'term'         => 86400,
            'explain'      => 0,
            'created_at'   => now(),
        ]);
    }

    public function testBanPageIsShown(): void
    {
        $this->actingAs($this->user)
            ->get('/ban')
            ->assertOk()
            ->assertSee('Причина бана');
    }

    public function testExpiredBanIsLifted(): void
    {
        $this->user->update(['timeban' => now()->subMinute()]);

        $this->actingAs($this->user)
            ->get('/ban')
            ->assertRedirect('/');

        $this->assertSame(User::USER, $this->user->fresh()->level);
    }

    public function testExplainIsSentToAdmins(): void
    {
        $this->actingAs($this->user)
            ->post('/ban', ['msg' => 'Я больше не буду'])
            ->assertRedirect('ban')
            ->assertSessionHas('success');

        $this->assertSame(1, (int) $this->banhist->fresh()->explain);
        $this->assertTrue(Dialogue::query()->where('user_id', $this->admin->id)->exists());
    }

    public function testShortExplainIsFieldError(): void
    {
        // Раньше ошибка уходила под ключ text, и поле msg не подсвечивалось
        $this->actingAs($this->user)
            ->post('/ban', ['msg' => 'Ой'])
            ->assertRedirect('ban')
            ->assertSessionHasErrors(['msg' => __('validator.text')]);

        $this->assertSame(0, (int) $this->banhist->fresh()->explain);
    }

    public function testRepeatedExplainFails(): void
    {
        $this->banhist->update(['explain' => 1]);

        $this->actingAs($this->user)
            ->post('/ban', ['msg' => 'Я больше не буду'])
            ->assertSessionHasErrors();

        $this->assertSame(__('users.explain_repeat'), session('errors')->first());
    }

    public function testExplainIsForbiddenBySetting(): void
    {
        $this->overrideSetting('addbansend', 0);

        $this->actingAs($this->user)
            ->post('/ban', ['msg' => 'Я больше не буду'])
            ->assertSessionHasErrors();

        $this->assertSame(__('users.explain_forbidden'), session('errors')->first());
        $this->assertSame(0, (int) $this->banhist->fresh()->explain);
    }

    public function testNotBannedUserCannotExplain(): void
    {
        $this->actingAs($this->admin)
            ->post('/ban', ['msg' => 'Я больше не буду'])
            ->assertSee(__('users.not_banned'));
    }
}
