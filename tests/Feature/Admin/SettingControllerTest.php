<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Настройки лежат в группе check.admin:boss (routes/admin.php:147)
        $this->boss = User::factory()->boss()->create(['login' => 'boss_setting']);
    }

    public function testIndexIsShown(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin/settings')
            ->assertOk();
    }

    public function testIndexWithUnknownActionReturns404(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin/settings?act=nonexistent')
            ->assertNotFound();
    }

    public function testSaveSettings(): void
    {
        $response = $this->actingAs($this->boss)
            ->post('/admin/settings?act=mains', [
                'sets' => ['title' => 'Новое название сайта'],
            ]);

        $response->assertRedirect('admin/settings?act=mains');

        $this->assertDatabaseHas('settings', [
            'name'  => 'title',
            'value' => 'Новое название сайта',
        ]);

        $response->assertSessionHas('success');
    }

    public function testSaveSettingsAppliesMultiplier(): void
    {
        $this->actingAs($this->boss)
            ->post('/admin/settings?act=mains', [
                'sets' => ['title' => '5'],
                'mods' => ['title' => 3],
            ]);

        $this->assertDatabaseHas('settings', [
            'name'  => 'title',
            'value' => 15,
        ]);
    }

    public function testSaveEmptyRequiredSettingFails(): void
    {
        $before = Setting::query()->where('name', 'title')->value('value');

        $response = $this->actingAs($this->boss)
            ->post('/admin/settings?act=mains', [
                'sets' => ['title' => ''],
            ]);

        $this->assertDatabaseHas('settings', [
            'name'  => 'title',
            'value' => $before,
        ]);

        $response->assertSessionHasErrors('sets[title]');
    }

    public function testSaveWithoutSetsFails(): void
    {
        $response = $this->actingAs($this->boss)
            ->post('/admin/settings?act=mains', []);

        $response->assertSessionHasErrors('sets');
    }

    public function testShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->boss)
            ->from('/admin/settings?act=mains')
            ->post('/admin/settings?act=mains', [
                'sets' => ['title' => ''],
            ]);

        $response->assertRedirect('/admin/settings?act=mains');

        $this->actingAs($this->boss)
            ->get('/admin/settings?act=mains')
            ->assertOk()
            ->assertSee('is-invalid');
    }
}
