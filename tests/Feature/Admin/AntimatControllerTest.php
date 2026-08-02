<?php

namespace Tests\Feature\Admin;

use App\Models\Antimat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntimatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_antimat']);
    }

    public function testIndexShowsWords(): void
    {
        Antimat::query()->create(['string' => 'тестослово']);

        $this->actingAs($this->admin)
            ->get('/admin/antimat')
            ->assertOk()
            ->assertSee('тестослово');
    }

    public function testAddWord(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/antimat', ['word' => 'тестослово']);

        $response->assertRedirect('admin/antimat');

        $this->assertDatabaseHas('antimat', ['string' => 'тестослово']);

        $response->assertSessionHas('success');
    }

    public function testAddWordIsLowercased(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/antimat', ['word' => 'ТестоСлово']);

        // Сравниваем в PHP: у таблицы case-insensitive collation,
        // assertDatabaseHas прошёл бы и на нетронутом регистре
        $this->assertSame('тестослово', Antimat::query()->value('string'));
    }

    public function testAddEmptyWordFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/antimat', ['word' => '']);

        $this->assertDatabaseCount('antimat', 0);

        $response->assertSessionHasErrors();
    }

    public function testAddDuplicateWordFails(): void
    {
        Antimat::query()->create(['string' => 'тестослово']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/antimat', ['word' => 'тестослово']);

        $this->assertDatabaseCount('antimat', 1);

        $response->assertSessionHasErrors();
    }

    public function testAddWordShowsErrorAfterRedirect(): void
    {
        Antimat::query()->create(['string' => 'тестослово']);

        $response = $this->actingAs($this->admin)
            ->from('/admin/antimat')
            ->post('/admin/antimat', ['word' => 'тестослово']);

        $response->assertRedirect('/admin/antimat');

        $this->actingAs($this->admin)
            ->get('/admin/antimat')
            ->assertOk()
            ->assertSee(__('admin.antimat.word_listed'));
    }

    public function testDeleteWord(): void
    {
        $word = Antimat::query()->create(['string' => 'тестослово']);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/antimat/delete', ['id' => $word->id]);

        $response->assertRedirect('admin/antimat');

        $this->assertDatabaseMissing('antimat', ['id' => $word->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteMissingWordFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete('/admin/antimat/delete', ['id' => 999999]);

        $response->assertRedirect('admin/antimat');

        $response->assertSessionHasErrors();
    }

    public function testClearAsBoss(): void
    {
        Antimat::query()->create(['string' => 'тестослово']);

        $boss = User::factory()->boss()->create(['login' => 'boss_antimat']);

        $response = $this->actingAs($boss)
            ->post('/admin/antimat/clear');

        $response->assertRedirect(route('admin.antimat.index'));

        $this->assertDatabaseCount('antimat', 0);

        $response->assertSessionHas('success');
    }

    public function testClearAsAdminIsForbidden(): void
    {
        Antimat::query()->create(['string' => 'тестослово']);

        $response = $this->actingAs($this->admin)
            ->post('/admin/antimat/clear');

        $this->assertDatabaseCount('antimat', 1);

        $response->assertSessionHasErrors();
    }
}
