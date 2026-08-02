<?php

namespace Tests\Feature\Admin;

use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_rules']);
    }

    public function testIndexShowsRules(): void
    {
        Rule::query()->delete();
        Rule::query()->create(['text' => 'Тестовые-правила', 'created_at' => now()]);

        $this->actingAs($this->admin)
            ->get('/admin/rules')
            ->assertOk()
            ->assertSee('Тестовые-правила');
    }

    public function testIndexReplacesSitenamePlaceholder(): void
    {
        Rule::query()->delete();
        Rule::query()->create(['text' => 'Правила сайта %SITENAME%', 'created_at' => now()]);

        $this->actingAs($this->admin)
            ->get('/admin/rules')
            ->assertOk()
            ->assertSee(setting('title'))
            ->assertDontSee('%SITENAME%');
    }

    public function testEditFormShowsCurrentRules(): void
    {
        Rule::query()->delete();
        Rule::query()->create(['text' => 'Тестовые-правила', 'created_at' => now()]);

        $this->actingAs($this->admin)
            ->get('/admin/rules/edit')
            ->assertOk()
            ->assertSee('Тестовые-правила');
    }

    public function testEditSavesRules(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/rules/edit', ['msg' => 'Обновлённые-правила']);

        $response->assertRedirect('admin/rules');

        $this->assertStringContainsString(
            'Обновлённые-правила',
            (string) Rule::query()->value('text'),
        );

        $response->assertSessionHas('success');
    }

    public function testEditCreatesRulesWhenMissing(): void
    {
        Rule::query()->delete();

        $this->actingAs($this->admin)
            ->post('/admin/rules/edit', ['msg' => 'Первые-правила']);

        $this->assertDatabaseCount('rules', 1);

        $this->assertStringContainsString(
            'Первые-правила',
            (string) Rule::query()->value('text'),
        );
    }

    public function testEditWithEmptyTextFails(): void
    {
        Rule::query()->delete();
        Rule::query()->create(['text' => 'Тестовые-правила', 'created_at' => now()]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/rules/edit', ['msg' => '']);

        $this->assertStringContainsString(
            'Тестовые-правила',
            (string) Rule::query()->value('text'),
        );

        $response->assertSessionHasErrors('msg');
    }

    public function testEditShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/rules/edit')
            ->post('/admin/rules/edit', ['msg' => '']);

        $response->assertRedirect('/admin/rules/edit');

        $this->actingAs($this->admin)
            ->get('/admin/rules/edit')
            ->assertOk()
            ->assertSee(__('admin.rules.rules_empty'))
            ->assertSee('is-invalid');
    }

    public function testEditRequiresAdmin(): void
    {
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($user)
            ->get('/admin/rules/edit')
            ->assertForbidden();
    }
}
