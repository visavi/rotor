<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglistControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_reglist']);
    }

    private function makePendedUser(string $login = 'pended_user'): User
    {
        return User::factory()->create([
            'login' => $login,
            'level' => User::PENDED,
        ]);
    }

    public function testIndexShowsPendedUsers(): void
    {
        $this->makePendedUser();

        $this->actingAs($this->admin)
            ->get('/admin/reglists')
            ->assertOk()
            ->assertSee('pended_user');
    }

    public function testApproveUsers(): void
    {
        $user = $this->makePendedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/reglists', [
                'choice' => [$user->id],
                'action' => 'yes',
            ]);

        $response->assertRedirect('admin/reglists?page=1');

        $user->refresh();
        $this->assertSame(User::USER, $user->level);

        $response->assertSessionHas('success');
    }

    public function testDeleteUsers(): void
    {
        $user = $this->makePendedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/reglists', [
                'choice' => [$user->id],
                'action' => 'no',
            ]);

        $response->assertRedirect('admin/reglists?page=1');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $response->assertSessionHas('success');
    }

    public function testWithoutSelectionFails(): void
    {
        $user = $this->makePendedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/reglists', ['action' => 'yes']);

        $user->refresh();
        $this->assertSame(User::PENDED, $user->level);

        $response->assertSessionHasErrors();
    }

    public function testWithInvalidActionFails(): void
    {
        $user = $this->makePendedUser();

        $response = $this->actingAs($this->admin)
            ->post('/admin/reglists', [
                'choice' => [$user->id],
                'action' => 'maybe',
            ]);

        $user->refresh();
        $this->assertSame(User::PENDED, $user->level);

        $response->assertSessionHasErrors('action');
    }

    public function testShowsErrorOnFormAfterRedirect(): void
    {
        $this->makePendedUser();

        $response = $this->actingAs($this->admin)
            ->from('/admin/reglists')
            ->post('/admin/reglists', ['action' => 'yes']);

        $response->assertRedirect('/admin/reglists');

        $this->actingAs($this->admin)
            ->get('/admin/reglists')
            ->assertOk()
            ->assertSee(__('admin.reglists.users_not_selected'));
    }
}
