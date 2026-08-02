<?php

namespace Tests\Feature\Admin;

use App\Models\Ban;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpBanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_ipban']);
    }

    private function makeBan(string $ip = '10.11.12.13'): Ban
    {
        return Ban::query()->create([
            'ip'         => $ip,
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);
    }

    public function testIndexShowsBannedIps(): void
    {
        $this->makeBan();

        $this->actingAs($this->admin)
            ->get('/admin/ipbans')
            ->assertOk()
            ->assertSee('10.11.12.13');
    }

    public function testAddIp(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans', ['ip' => '10.11.12.13']);

        $response->assertRedirect('admin/ipbans');

        $this->assertDatabaseHas('ban', [
            'ip'      => '10.11.12.13',
            'user_id' => $this->admin->id,
        ]);

        $response->assertSessionHas('success');
    }

    public function testAddInvalidIpFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans', ['ip' => '999.1.1.1']);

        $this->assertDatabaseCount('ban', 0);

        $response->assertSessionHasErrors('ip');
    }

    public function testAddDuplicateIpFails(): void
    {
        $this->makeBan();

        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans', ['ip' => '10.11.12.13']);

        $this->assertDatabaseCount('ban', 1);

        $response->assertSessionHasErrors('ip');
    }

    public function testAddShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/ipbans')
            ->post('/admin/ipbans', ['ip' => '999.1.1.1']);

        $response->assertRedirect('/admin/ipbans');

        $this->actingAs($this->admin)
            ->get('/admin/ipbans')
            ->assertOk()
            ->assertSee(__('admin.ipbans.ip_invalid'))
            ->assertSee('is-invalid')
            ->assertSee('value="999.1.1.1"', false);
    }

    public function testDeleteIps(): void
    {
        $ban = $this->makeBan();

        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans/delete', ['del' => [$ban->id]]);

        $response->assertRedirect('admin/ipbans?page=1');

        $this->assertDatabaseMissing('ban', ['id' => $ban->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteWithoutSelectionFails(): void
    {
        $this->makeBan();

        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans/delete', []);

        $this->assertDatabaseCount('ban', 1);

        $response->assertSessionHasErrors();
    }

    public function testClearAsBoss(): void
    {
        $this->makeBan();

        $boss = User::factory()->boss()->create(['login' => 'boss_ipban']);

        $response = $this->actingAs($boss)
            ->post('/admin/ipbans/clear');

        $response->assertRedirect(route('admin.ipbans.index'));

        $this->assertDatabaseCount('ban', 0);

        $response->assertSessionHas('success');
    }

    public function testClearAsAdminIsForbidden(): void
    {
        $this->makeBan();

        $response = $this->actingAs($this->admin)
            ->post('/admin/ipbans/clear');

        $this->assertDatabaseCount('ban', 1);

        $response->assertSessionHasErrors();
    }
}
