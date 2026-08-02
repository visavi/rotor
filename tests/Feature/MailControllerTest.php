<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        // captchaVerify() возвращает true только при отключённой капче
        $this->overrideSetting('captcha_type', 'disable');

        Mail::fake();
    }

    public function testIndexIsShown(): void
    {
        $this->get('/mails')->assertOk();
    }

    public function testSendMailAsGuest(): void
    {
        $response = $this->post('/mails', [
            'name'    => 'Тестовый отправитель',
            'email'   => 'sender@example.com',
            'message' => 'Текст обращения в поддержку',
        ]);

        $response->assertRedirect('/mails');
        $response->assertSessionHas('success');
    }

    public function testSendMailWithShortMessageFails(): void
    {
        $response = $this->post('/mails', [
            'name'    => 'Тестовый отправитель',
            'email'   => 'sender@example.com',
            'message' => 'ab',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function testSendMailWithInvalidEmailFails(): void
    {
        $response = $this->post('/mails', [
            'name'    => 'Тестовый отправитель',
            'email'   => 'не-адрес',
            'message' => 'Текст обращения в поддержку',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function testSendMailKeepsInputOnError(): void
    {
        $response = $this->post('/mails', [
            'name'    => 'Тестовый отправитель',
            'email'   => 'sender@example.com',
            'message' => 'ab',
        ]);

        $response->assertSessionHasInput('name', 'Тестовый отправитель');
        $response->assertSessionHasInput('email', 'sender@example.com');
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
