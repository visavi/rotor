<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MailFailureTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->boss = User::factory()->boss()->create(['login' => 'boss_mail']);
        $this->path = storage_path('framework/mail-failure');

        // Метка живёт файлом и переживает тесты, поэтому убираем её заранее
        @unlink($this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    public function testWarningHiddenWithoutFailures(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.mail_failed'));
    }

    public function testWarningShownAfterFailure(): void
    {
        app(MailService::class)->markFailure(
            new RuntimeException('Connection could not be established'),
            ['to' => 'user@example.com'],
        );

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('index.mail_failed'))
            ->assertSee('Connection could not be established');
    }

    public function testWarningHiddenAfterSuccess(): void
    {
        $mail = app(MailService::class);
        $mail->markFailure(new RuntimeException('Connection refused'));
        $mail->markSuccess();

        $this->actingAs($this->boss)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.mail_failed'));
    }

    public function testWarningHiddenFromAdmin(): void
    {
        $admin = User::factory()->admin()->create(['login' => 'admin_mail']);

        // Почту чинит владелец сайта, остальным сообщение бесполезно
        app(MailService::class)->markFailure(new RuntimeException('Connection refused'));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('index.mail_failed'));
    }
}
