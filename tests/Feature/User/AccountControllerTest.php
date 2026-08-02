<?php

namespace Tests\Feature\User;

use App\Models\BlackList;
use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Mail::fake();

        $this->user = User::factory()->create([
            'login'    => 'account_user',
            'email'    => 'account@example.com',
            'password' => Hash::make('secret123'),
            'point'    => 5000,
            'money'    => 50000,
            // Иначе User::gettingBonus() начислит ежедневный бонус и собьёт баланс
            'timebonus' => now(),
        ]);
    }

    public function testAccountPageIsShown(): void
    {
        $this->actingAs($this->user)
            ->get('/accounts')
            ->assertOk();
    }

    public function testAccountPageRequiresAuth(): void
    {
        $this->get('/accounts')->assertForbidden();
    }

    public function testChangeMailCreatesRequest(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/changemail', [
                'email'    => 'new-account@example.com',
                'password' => 'secret123',
            ]);

        $response->assertRedirect('accounts');

        $this->assertDatabaseHas('email_changes', [
            'user_id' => $this->user->id,
            'email'   => 'new-account@example.com',
        ]);

        $response->assertSessionHas('success');
    }

    public function testChangeMailWithWrongPasswordFails(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/changemail', [
                'email'    => 'new-account@example.com',
                'password' => 'wrong-password',
            ]);

        $this->assertDatabaseCount('email_changes', 0);

        $response->assertSessionHasErrors('password');
    }

    public function testChangeMailToExistingEmailFails(): void
    {
        User::factory()->create([
            'login' => 'other_user',
            'email' => 'taken@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->post('/accounts/changemail', [
                'email'    => 'taken@example.com',
                'password' => 'secret123',
            ]);

        $this->assertDatabaseCount('email_changes', 0);

        $response->assertSessionHasErrors('email');
    }

    public function testChangeMailToBlacklistedEmailFails(): void
    {
        BlackList::query()->create([
            'type'       => 'email',
            'value'      => 'spam@example.com',
            'user_id'    => $this->user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->post('/accounts/changemail', [
                'email'    => 'spam@example.com',
                'password' => 'secret123',
            ]);

        $this->assertDatabaseCount('email_changes', 0);

        $response->assertSessionHasErrors('email');
    }

    public function testChangeMailKeepsInputOnError(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/changemail', [
                'email'    => 'new-account@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasInput('email', 'new-account@example.com');
    }

    public function testEditMailAppliesChange(): void
    {
        EmailChange::query()->create([
            'user_id'    => $this->user->id,
            'email'      => 'new-account@example.com',
            'token'      => 'token-for-test',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/accounts/editmail/token-for-test');

        $response->assertRedirect(route('accounts.account'));

        $this->user->refresh();
        $this->assertSame('new-account@example.com', $this->user->email);

        $this->assertDatabaseCount('email_changes', 0);

        $response->assertSessionHas('success');
    }

    public function testEditStatus(): void
    {
        $moneyBefore = $this->user->money;

        $response = $this->actingAs($this->user)
            ->post('/accounts/editstatus', ['status' => 'Тестовый']);

        $response->assertRedirect('accounts');

        $this->user->refresh();

        $this->assertSame('Тестовый', $this->user->status);
        $this->assertSame($moneyBefore - (int) setting('editstatusmoney'), $this->user->money);

        $response->assertSessionHas('success');
    }

    public function testEditStatusWithoutEnoughPointsFails(): void
    {
        $this->user->update(['point' => 0]);

        $response = $this->actingAs($this->user)
            ->post('/accounts/editstatus', ['status' => 'Тестовый']);

        $this->user->refresh();
        $this->assertNull($this->user->status);

        $response->assertSessionHasErrors('status');
    }

    public function testEditStatusWithoutEnoughMoneyFails(): void
    {
        $this->user->update(['money' => 0]);

        $response = $this->actingAs($this->user)
            ->post('/accounts/editstatus', ['status' => 'Тестовый']);

        $this->user->refresh();
        $this->assertNull($this->user->status);

        $response->assertSessionHasErrors('status');
    }

    public function testEditColor(): void
    {
        $moneyBefore = $this->user->money;

        $response = $this->actingAs($this->user)
            ->post('/accounts/editcolor', ['color' => '#123456']);

        $response->assertRedirect('accounts');

        $this->user->refresh();

        $this->assertSame('#123456', $this->user->color);
        $this->assertSame($moneyBefore - (int) setting('editcolormoney'), $this->user->money);

        $response->assertSessionHas('success');
    }

    public function testEditColorWithInvalidValueFails(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/editcolor', ['color' => 'not-a-color']);

        $this->user->refresh();
        $this->assertNull($this->user->color);

        $response->assertSessionHasErrors('color');
    }

    public function testEditColorKeepsInputOnError(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/editcolor', ['color' => 'not-a-color']);

        $response->assertSessionHasInput('color', 'not-a-color');
    }

    public function testApikeyCreate(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/apikey', ['action' => 'create']);

        $response->assertRedirect('accounts');

        $this->user->refresh();
        $this->assertSame(32, strlen((string) $this->user->apikey));

        $response->assertSessionHas('success');
    }

    public function testApikeyDelete(): void
    {
        $this->user->update(['apikey' => str_repeat('a', 32)]);

        $response = $this->actingAs($this->user)
            ->post('/accounts/apikey', ['action' => 'delete']);

        $this->user->refresh();
        $this->assertSame('', (string) $this->user->apikey);

        $response->assertSessionHas('success');
    }

    public function testEditPassword(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/accounts/editpassword', [
                'old_password'     => 'secret123',
                'new_password'     => 'newsecret456',
                'confirm_password' => 'newsecret456',
            ]);

        $response->assertRedirect('/');

        $this->user->refresh();
        $this->assertTrue(Hash::check('newsecret456', $this->user->password));

        $response->assertSessionHas('success');
    }
}
