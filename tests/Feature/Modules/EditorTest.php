<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\ModuleTestCase;

class EditorTest extends ModuleTestCase
{
    protected string $moduleName = 'StyleEditor';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->admin()->create(['login' => 'admin_test']);
    }

    public function testEditorIndexRequiresAdmin(): void
    {
        $response = $this->get('/admin/editor');
        $response->assertRedirect();
    }

    public function testEditorIndexAccessibleByAdmin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/editor');
        $response->assertOk();
    }

    public function testEditorSaveRequiresAdmin(): void
    {
        $response = $this->post('/admin/editor', ['css' => '', 'js' => '']);
        $response->assertRedirect();
    }
}
