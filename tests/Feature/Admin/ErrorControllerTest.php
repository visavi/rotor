<?php

namespace Tests\Feature\Admin;

use App\Models\Error;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_error']);
    }

    private function makeError(): Error
    {
        return Error::query()->create([
            'code'       => 404,
            'request'    => '/test-not-found',
            'referer'    => '',
            'user_id'    => $this->admin->id,
            'ip'         => '10.11.12.13',
            'brow'       => 'TestBrowser',
            'created_at' => now(),
        ]);
    }

    public function testIndexShowsErrors(): void
    {
        $this->makeError();

        $this->actingAs($this->admin)
            ->get('/admin/errors')
            ->assertOk()
            ->assertSee('/test-not-found');
    }

    public function testClearAsBoss(): void
    {
        $this->makeError();

        $boss = User::factory()->boss()->create(['login' => 'boss_error']);

        $response = $this->actingAs($boss)
            ->post('/admin/errors/clear');

        $response->assertRedirect(route('admin.errors.index'));

        $this->assertDatabaseCount('errors', 0);

        $response->assertSessionHas('success');
    }

    public function testClearAsAdminIsForbidden(): void
    {
        $this->makeError();

        $response = $this->actingAs($this->admin)
            ->post('/admin/errors/clear');

        $this->assertDatabaseCount('errors', 1);

        $response->assertSessionHasErrors();
    }
}
