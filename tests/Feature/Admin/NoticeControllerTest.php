<?php

namespace Tests\Feature\Admin;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Шаблоны писем лежат в группе check.admin:boss (routes/admin.php:147)
        $this->admin = User::factory()->boss()->create(['login' => 'boss_notice']);
    }

    private function makeNotice(array $attributes = []): Notice
    {
        return Notice::query()->create(array_merge([
            'type'       => 'test_notice',
            'name'       => 'Тестовый шаблон',
            'text'       => 'Текст тестового шаблона',
            'user_id'    => $this->admin->id,
            'protect'    => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    public function testIndexShowsNotices(): void
    {
        $this->makeNotice();

        $this->actingAs($this->admin)
            ->get('/admin/notices')
            ->assertOk()
            ->assertSee('Тестовый шаблон');
    }

    public function testCreateNotice(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/notices/create', [
            'type' => 'test_notice',
            'name' => 'Тестовый шаблон',
            'text' => 'Текст тестового шаблона',
        ]);

        $this->assertDatabaseHas('notices', [
            'type'    => 'test_notice',
            'name'    => 'Тестовый шаблон',
            'user_id' => $this->admin->id,
            'protect' => 0,
        ]);

        $notice = Notice::query()->where('type', 'test_notice')->firstOrFail();

        $response->assertRedirect('admin/notices/edit/' . $notice->id);
        $response->assertSessionHas('success');
    }

    public function testCreateNoticeWithProtectFlag(): void
    {
        $this->actingAs($this->admin)->post('/admin/notices/create', [
            'type'    => 'test_notice',
            'name'    => 'Тестовый шаблон',
            'text'    => 'Текст тестового шаблона',
            'protect' => '1',
        ]);

        $this->assertDatabaseHas('notices', [
            'type'    => 'test_notice',
            'protect' => 1,
        ]);
    }

    public function testCreateNoticeWithInvalidTypeFails(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/notices/create', [
            'type' => 'тип шаблона!',
            'name' => 'Тестовый шаблон',
            'text' => 'Текст тестового шаблона',
        ]);

        $this->assertDatabaseMissing('notices', ['name' => 'Тестовый шаблон']);

        $response->assertSessionHasErrors('type');
    }

    public function testCreateNoticeWithDuplicateTypeFails(): void
    {
        $this->makeNotice();

        $response = $this->actingAs($this->admin)->post('/admin/notices/create', [
            'type' => 'test_notice',
            'name' => 'Другое название',
            'text' => 'Другой текст шаблона',
        ]);

        $this->assertDatabaseMissing('notices', ['name' => 'Другое название']);

        $response->assertSessionHasErrors('type');
    }

    public function testCreateNoticeWithShortNameFails(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/notices/create', [
            'type' => 'test_notice',
            'name' => 'ab',
            'text' => 'Текст тестового шаблона',
        ]);

        $this->assertDatabaseMissing('notices', ['type' => 'test_notice']);

        $response->assertSessionHasErrors('name');
    }

    public function testCreateNoticeShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/notices/create')
            ->post('/admin/notices/create', [
                'type' => 'test_notice',
                'name' => 'ab',
                'text' => 'Текст тестового шаблона',
            ]);

        $response->assertRedirect('/admin/notices/create');

        $this->actingAs($this->admin)
            ->get('/admin/notices/create')
            ->assertOk()
            ->assertSee('is-invalid')
            ->assertSee('value="test_notice"', false)
            ->assertSee('value="ab"', false);
    }

    public function testEditNotice(): void
    {
        $notice = $this->makeNotice();

        $response = $this->actingAs($this->admin)->post('/admin/notices/edit/' . $notice->id, [
            'name' => 'Новое название',
            'text' => 'Новый текст шаблона',
        ]);

        $response->assertRedirect('admin/notices/edit/' . $notice->id);

        $this->assertDatabaseHas('notices', [
            'id'   => $notice->id,
            'name' => 'Новое название',
        ]);

        $response->assertSessionHas('success');
    }

    public function testEditMissingNoticeReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/notices/edit/999999')
            ->assertNotFound();
    }

    public function testEditNoticeWithShortTextFails(): void
    {
        $notice = $this->makeNotice();

        $response = $this->actingAs($this->admin)->post('/admin/notices/edit/' . $notice->id, [
            'name' => 'Новое название',
            'text' => 'ab',
        ]);

        $this->assertDatabaseHas('notices', [
            'id'   => $notice->id,
            'name' => 'Тестовый шаблон',
        ]);

        $response->assertSessionHasErrors('text');
    }

    public function testDeleteNotice(): void
    {
        $notice = $this->makeNotice();

        $response = $this->actingAs($this->admin)
            ->delete('/admin/notices/delete/' . $notice->id);

        $response->assertRedirect('admin/notices');

        $this->assertDatabaseMissing('notices', ['id' => $notice->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteProtectedNoticeFails(): void
    {
        $notice = $this->makeNotice(['protect' => 1]);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/notices/delete/' . $notice->id);

        $response->assertRedirect('admin/notices');

        $this->assertDatabaseHas('notices', ['id' => $notice->id]);

        $response->assertSessionHasErrors();
    }

    public function testDeleteMissingNoticeFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete('/admin/notices/delete/999999');

        $response->assertRedirect('admin/notices');

        $response->assertSessionHasErrors();
    }
}
