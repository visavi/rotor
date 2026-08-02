<?php

namespace Tests\Feature\Admin;

use App\Models\Banhist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanhistControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_banhist']);
    }

    private function makeBanhist(User $user): Banhist
    {
        return Banhist::query()->create([
            'user_id'      => $user->id,
            'send_user_id' => $this->admin->id,
            'type'         => Banhist::BAN,
            'reason'       => 'Причина бана в истории',
            'term'         => 86400,
            'created_at'   => now(),
        ]);
    }

    public function testIndexShowsHistory(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);
        $this->makeBanhist($user);

        $this->actingAs($this->admin)
            ->get('/admin/banhists')
            ->assertOk()
            ->assertSee('plain_user');
    }

    public function testDeleteRecords(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);
        $banhist = $this->makeBanhist($user);

        $response = $this->actingAs($this->admin)
            ->post('/admin/banhists/delete', ['del' => [$banhist->id]]);

        $response->assertRedirect('admin/banhists?page=1');

        $this->assertDatabaseMissing('banhist', ['id' => $banhist->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteRedirectsToUserViewWhenLoginGiven(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);
        $banhist = $this->makeBanhist($user);

        $response = $this->actingAs($this->admin)
            ->post('/admin/banhists/delete', [
                'del'  => [$banhist->id],
                'user' => $user->login,
            ]);

        $response->assertRedirect('admin/banhists/view?user=plain_user&page=1');

        $this->assertDatabaseMissing('banhist', ['id' => $banhist->id]);
    }

    public function testDeleteWithoutSelectionFails(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);
        $this->makeBanhist($user);

        $response = $this->actingAs($this->admin)
            ->post('/admin/banhists/delete', []);

        $this->assertDatabaseCount('banhist', 1);

        $response->assertSessionHasErrors();
    }
}
