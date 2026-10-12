<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
    }

    public function testOldFeedbackAddressWithoutModule(): void
    {
        // Форма в модуле Feedback: без него старый адрес не ведёт никуда
        $this->get('/mails')->assertNotFound();
    }

    public function testRecoveryHidesFeedbackLinkWithoutModule(): void
    {
        $this->get('/recovery')
            ->assertOk()
            ->assertDontSee(__('mails.recovery_text1'));
    }

    public function testUnsubscribe(): void
    {
        $user = User::factory()->create([
            'login'     => 'plain_user',
            'subscribe' => 'subscribe-key-test',
            'timebonus' => now(),
        ]);

        $response = $this->get('/unsubscribe?key=subscribe-key-test');

        $response->assertRedirect('/');

        $user->refresh();
        $this->assertNull($user->subscribe);

        $response->assertSessionHas('success');
    }

    public function testUnsubscribeWithUnknownKeyIsRejected(): void
    {
        $this->get('/unsubscribe?key=nonexistent')
            ->assertOk()
            ->assertSee(__('mails.token_expired'));
    }
}
