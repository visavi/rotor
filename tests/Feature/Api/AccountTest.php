<?php

namespace Tests\Feature\Api;

use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('filesize', 10485760);
        $this->overrideSetting('media_extensions', 'jpg,png');
        $this->overrideSetting('screensize', 1000);
        $this->overrideSetting('editstatuspoint', 0);
        $this->overrideSetting('editstatusmoney', 0);
        $this->overrideSetting('editcolorpoint', 0);
        $this->overrideSetting('editcolormoney', 0);

        $this->user = User::factory()->create([
            'apikey'   => Str::random(32),
            'password' => Hash::make('secret123'),
        ]);
    }

    public function testProfileRequiresToken(): void
    {
        $this->patchJson('/api/user', ['name' => 'Пользователь'])->assertStatus(400);
    }

    public function testProfileIsUpdated(): void
    {
        $this->patchJson('/api/user', [
            'name'     => 'Пользователь',
            'country'  => 'Россия',
            'city'     => 'Москва',
            'site'     => 'https://example.com',
            'birthday' => '01.02.1990',
            'info'     => 'О себе',
            'gender'   => User::FEMALE,
        ], $this->headers())
            ->assertOk()
            ->assertJsonPath('data.name', 'Пользователь')
            ->assertJsonPath('data.city', 'Москва');

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'gender' => User::FEMALE]);
    }

    public function testInvalidSiteIsRejected(): void
    {
        $this->patchJson('/api/user', ['site' => 'не ссылка'], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('site');
    }

    public function testSettingsListAvailableValues(): void
    {
        $this->getJson('/api/user/settings', $this->headers())
            ->assertOk()
            ->assertJsonStructure([
                'data'      => ['themes', 'language', 'timezone', 'notify_mention', 'subscribe'],
                'available' => ['themes', 'languages', 'timezones'],
            ]);
    }

    public function testSettingsAreUpdated(): void
    {
        $this->patchJson('/api/user/settings', [
            'language'       => 'ru',
            'timezone'       => 3,
            'themes'         => 'default',
            'notify_comment' => true,
            'subscribe'      => true,
        ], $this->headers())->assertOk();

        $user = $this->user->fresh();

        $this->assertSame(3, (int) $user->timezone);
        $this->assertSame(1, (int) $user->notify_comment);
        $this->assertNotNull($user->subscribe);
    }

    public function testUnknownLanguageIsRejected(): void
    {
        $this->patchJson('/api/user/settings', ['language' => 'zz', 'timezone' => 0], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('language');
    }

    public function testPasswordIsChanged(): void
    {
        $this->postJson('/api/user/password', [
            'old_password'     => 'secret123',
            'new_password'     => 'newsecret1',
            'confirm_password' => 'newsecret1',
        ], $this->headers())->assertOk();

        $this->assertTrue(Hash::check('newsecret1', $this->user->fresh()->password));
        // Токен переживает смену пароля, иначе приложение вылетало бы из аккаунта
        $this->assertSame($this->user->apikey, $this->user->fresh()->apikey);
    }

    public function testWrongOldPasswordIsRejected(): void
    {
        $this->postJson('/api/user/password', [
            'old_password'     => 'wrong',
            'new_password'     => 'newsecret1',
            'confirm_password' => 'newsecret1',
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('old_password');
    }

    public function testEmailChangeIsRequested(): void
    {
        $this->postJson('/api/user/email', [
            'email'    => 'other@example.com',
            'password' => 'secret123',
        ], $this->headers())->assertOk();

        $this->assertDatabaseHas('email_changes', [
            'user_id' => $this->user->id,
            'email'   => 'other@example.com',
        ]);
    }

    public function testSecondEmailChangeIsBlocked(): void
    {
        EmailChange::query()->create([
            'user_id'    => $this->user->id,
            'email'      => 'other@example.com',
            'token'      => 'token',
            'created_at' => now(),
        ]);

        $this->postJson('/api/user/email', [
            'email'    => 'third@example.com',
            'password' => 'secret123',
        ], $this->headers())->assertStatus(422);
    }

    public function testStatusAndColorAreChanged(): void
    {
        $this->postJson('/api/user/status', ['status' => 'Новый статус'], $this->headers())->assertOk();
        $this->postJson('/api/user/color', ['color' => '#ff0000'], $this->headers())->assertOk();

        $user = $this->user->fresh();

        $this->assertSame('Новый статус', $user->status);
        $this->assertSame('#ff0000', $user->color);
    }

    public function testStatusRequiresPoints(): void
    {
        $this->overrideSetting('editstatuspoint', 100);

        $this->postJson('/api/user/status', ['status' => 'Новый статус'], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function testApikeyIsRegenerated(): void
    {
        $response = $this->postJson('/api/user/apikey', [], $this->headers())->assertOk();

        $token = $response->json('token');

        $this->assertNotSame($this->user->apikey, $token);
        $this->assertSame($token, $this->user->fresh()->apikey);
    }

    public function testApikeyIsDeleted(): void
    {
        $this->postJson('/api/user/apikey', ['action' => 'delete'], $this->headers())
            ->assertOk()
            ->assertJsonPath('token', null);

        // Старый токен больше не пускает
        $this->getJson('/api/user', $this->headers())->assertStatus(401);
    }

    public function testPhotoIsUploadedAndDeleted(): void
    {
        $response = $this->post('/api/user/photo', [
            'photo' => UploadedFile::fake()->image('me.jpg', 300, 300),
        ], $this->headers())->assertOk();

        $user = $this->user->fresh();

        $this->assertNotNull($user->picture);
        $this->assertNotNull($user->avatar);
        $this->assertSame(url($user->avatar), $response->json('avatar'));

        $this->deleteJson('/api/user/photo', [], $this->headers())->assertOk();

        $this->assertNull($this->user->fresh()->picture);
    }

    public function testSmallPhotoIsRejected(): void
    {
        $this->post('/api/user/photo', [
            'photo' => UploadedFile::fake()->image('me.jpg', 50, 50),
        ], $this->headers())
            ->assertStatus(422)
            ->assertJsonValidationErrors('photo');
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->user->apikey,
            // Загрузка файла идёт обычной формой, ошибки нужны ответом, а не редиректом
            'Accept' => 'application/json',
        ];
    }
}
