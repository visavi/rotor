<?php

namespace Tests\Feature\Admin;

use App\Models\ModuleRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ModuleRegistryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    private int $baseCount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Реестры лежат в группе check.admin:boss (routes/admin.php:147)
        $this->boss = User::factory()->boss()->create(['login' => 'boss_registry']);

        // Реестры приходят из сидера, считаем прирост относительно базы
        $this->baseCount = ModuleRegistry::query()->count();
    }

    public function testIndexShowsRegistries(): void
    {
        ModuleRegistry::query()->create([
            'url'        => 'https://registry.example.com/modules.json',
            'created_at' => now(),
        ]);

        $this->actingAs($this->boss)
            ->get(route('admin.registries.index'))
            ->assertOk()
            ->assertSee('registry.example.com');
    }

    public function testStoreWithInvalidUrlFails(): void
    {
        $response = $this->actingAs($this->boss)
            ->post(route('admin.registries.store'), ['url' => 'не-адрес']);

        $response->assertRedirect(route('admin.registries.index'));

        $this->assertSame($this->baseCount, ModuleRegistry::query()->count());

        $response->assertSessionHas('danger');
    }

    public function testStoreWithDuplicateUrlFails(): void
    {
        ModuleRegistry::query()->create([
            'url'        => 'https://registry.example.com/modules.json',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->boss)
            ->post(route('admin.registries.store'), [
                'url' => 'https://registry.example.com/modules.json',
            ]);

        $this->assertSame($this->baseCount + 1, ModuleRegistry::query()->count());

        $response->assertSessionHas('danger');
    }

    public function testStoreWithUnreachableUrlReportsFailure(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $response = $this->actingAs($this->boss)
            ->post(route('admin.registries.store'), [
                'url' => 'https://registry.example.com/modules.json',
            ]);

        $this->assertSame($this->baseCount + 1, ModuleRegistry::query()->count());

        $response->assertSessionHas('danger');
    }

    public function testDestroyRegistry(): void
    {
        $registry = ModuleRegistry::query()->create([
            'url'        => 'https://registry.example.com/modules.json',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->boss)
            ->delete(route('admin.registries.destroy', $registry->id));

        $response->assertRedirect(route('admin.registries.index'));

        $this->assertDatabaseMissing('module_registries', ['id' => $registry->id]);

        $response->assertSessionHas('success');
    }

    public function testToggleRegistry(): void
    {
        $registry = ModuleRegistry::query()->create([
            'url'        => 'https://registry.example.com/modules.json',
            'active'     => 1,
            'created_at' => now(),
        ]);

        $this->actingAs($this->boss)
            ->post(route('admin.registries.toggle', $registry->id));

        $registry->refresh();
        $this->assertFalse((bool) $registry->active);
    }
}
