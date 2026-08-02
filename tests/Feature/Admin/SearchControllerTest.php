<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Поиск лежит в группе check.admin:boss (routes/admin.php:147)
        $this->boss = User::factory()->boss()->create(['login' => 'boss_search']);
    }

    public function testIndexIsShown(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin/search')
            ->assertOk();
    }

    public function testImportRunsCommandAndRedirects(): void
    {
        Artisan::spy();

        $response = $this->actingAs($this->boss)
            ->post('/admin/search/import');

        $response->assertRedirect(route('admin.search.index'));

        Artisan::shouldHaveReceived('call')->with('search:import');

        $response->assertSessionHas('success');
    }
}
