<?php

namespace Tests\Feature;

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
}
