<?php

namespace Tests\Feature\Admin;

use App\Models\Spam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpamControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_spam']);
    }

    private function makeSpam(): Spam
    {
        return Spam::factory()->create([
            'relate_type' => 'comments',
            'relate_id'   => 1,
            'user_id'     => $this->admin->id,
            'path'        => '/news/1',
            'created_at'  => now(),
        ]);
    }

    public function testIndexOpens(): void
    {
        $this->makeSpam();

        $this->actingAs($this->admin)
            ->get('/admin/spam?type=comments')
            ->assertOk();
    }

    public function testIndexSkipsTypesOfDisabledModules(): void
    {
        $this->makeSpam();

        // Тип отключённого модуля, жалоб на него больше — раньше он выбирался вкладкой по умолчанию
        foreach ([1, 2] as $relateId) {
            Spam::factory()->create([
                'relate_type' => 'disabled_module',
                'relate_id'   => $relateId,
                'user_id'     => $this->admin->id,
                'path'        => '/disabled/' . $relateId,
                'created_at'  => now(),
            ]);
        }

        $this->actingAs($this->admin)
            ->get('/admin/spam')
            ->assertOk()
            ->assertViewHas('type', 'comments')
            ->assertViewHas('total', fn (array $total) => ! isset($total['disabled_module']));
    }

    public function testDeleteSpam(): void
    {
        $spam = $this->makeSpam();

        $this->actingAs($this->admin)
            ->post('/admin/spam/delete', ['id' => $spam->id], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('main.record_deleted_success'),
            ]);

        $this->assertDatabaseMissing('spam', ['id' => $spam->id]);
    }

    public function testDeleteWithoutIdFails(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/spam/delete', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson([
                'success' => false,
                'message' => __('validator.deletion'),
            ]);
    }

    public function testDeleteWithoutAjaxFails(): void
    {
        $spam = $this->makeSpam();

        $this->actingAs($this->admin)
            ->post('/admin/spam/delete', ['id' => $spam->id])
            ->assertJson([
                'success' => false,
                'message' => __('validator.not_ajax'),
            ]);

        $this->assertDatabaseHas('spam', ['id' => $spam->id]);
    }

    public function testDeleteMissingSpamSucceeds(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/spam/delete', ['id' => 999999], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson(['success' => true]);
    }

    public function testDeleteRequiresAdmin(): void
    {
        $spam = $this->makeSpam();
        $user = User::factory()->create(['login' => 'plain_user']);

        $this->actingAs($user)
            ->post('/admin/spam/delete', ['id' => $spam->id], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertForbidden();

        $this->assertDatabaseHas('spam', ['id' => $spam->id]);
    }
}
