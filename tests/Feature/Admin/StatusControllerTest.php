<?php

namespace Tests\Feature\Admin;

use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_status']);
    }

    public function testIndexShowsStatuses(): void
    {
        Status::query()->create([
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Бывалый',
            'color'   => '#ff0000',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/status')
            ->assertOk()
            ->assertSee('Бывалый');
    }

    public function testCreateStatus(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/status/create', [
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Тестовый-статус',
            'color'   => '#00ff00',
        ]);

        $response->assertRedirect('admin/status');

        $this->assertDatabaseHas('status', [
            'name'    => 'Тестовый-статус',
            'topoint' => 100,
            'point'   => 200,
            'color'   => '#00ff00',
        ]);

        $response->assertSessionHas('success');
    }

    public function testCreateStatusWithShortNameFails(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/status/create', [
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'ab',
            'color'   => '#00ff00',
        ]);

        $this->assertDatabaseMissing('status', ['name' => 'ab']);

        $response->assertSessionHasErrors('name');
    }

    public function testCreateStatusWithInvalidColorFails(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/status/create', [
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Тестовый-статус',
            'color'   => 'not-a-color',
        ]);

        $this->assertDatabaseMissing('status', ['name' => 'Тестовый-статус']);

        $response->assertSessionHasErrors('color');
    }

    public function testCreateStatusKeepsInputOnError(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/status/create', [
            'topoint' => 300,
            'point'   => 400,
            'name'    => 'ab',
            'color'   => '#123456',
        ]);

        $response->assertSessionHasInput('topoint', 300);
        $response->assertSessionHasInput('color', '#123456');
    }

    public function testCreateStatusShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/status/create')
            ->post('/admin/status/create', [
                'topoint' => 300,
                'point'   => 400,
                'name'    => 'ab',
                'color'   => '#123456',
            ]);

        $response->assertRedirect('/admin/status/create');

        $this->actingAs($this->admin)
            ->get('/admin/status/create')
            ->assertOk()
            ->assertSee(__('statuses.status_length'))
            ->assertSee('is-invalid')
            ->assertSee('value="300"', false)
            ->assertSee('value="#123456"', false);
    }

    public function testEditStatus(): void
    {
        $status = Status::query()->create([
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Старое',
            'color'   => '#ff0000',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/status/edit?id=' . $status->id, [
            'topoint' => 150,
            'point'   => 250,
            'name'    => 'Новое',
            'color'   => '#0000ff',
        ]);

        $response->assertRedirect('admin/status');

        $this->assertDatabaseHas('status', [
            'id'      => $status->id,
            'name'    => 'Новое',
            'topoint' => 150,
        ]);

        $response->assertSessionHas('success');
    }

    public function testEditMissingStatusReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/status/edit?id=999999')
            ->assertNotFound();
    }

    public function testEditStatusWithShortNameFails(): void
    {
        $status = Status::query()->create([
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Старое',
            'color'   => '#ff0000',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/status/edit?id=' . $status->id, [
            'topoint' => 150,
            'point'   => 250,
            'name'    => 'ab',
            'color'   => '#0000ff',
        ]);

        $this->assertDatabaseHas('status', [
            'id'   => $status->id,
            'name' => 'Старое',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function testDeleteStatus(): void
    {
        $status = Status::query()->create([
            'topoint' => 100,
            'point'   => 200,
            'name'    => 'Удаляемый',
            'color'   => '#ff0000',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/status/delete', ['id' => $status->id]);

        $response->assertRedirect('admin/status');

        $this->assertDatabaseMissing('status', ['id' => $status->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteMissingStatusFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete('/admin/status/delete', ['id' => 999999]);

        $response->assertRedirect('admin/status');
        $response->assertSessionHasErrors();
    }
}
