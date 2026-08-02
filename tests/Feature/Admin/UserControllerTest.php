<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $boss;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // Пользователи лежат в группе check.admin:boss (routes/admin.php:147)
        $this->boss = User::factory()->boss()->create(['login' => 'boss_users']);
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'login'     => 'plain_user',
            'email'     => 'plain@example.com',
            'timebonus' => now(),
        ], $attributes));
    }

    public function testIndexShowsUsers(): void
    {
        $user = $this->makeUser();

        $this->actingAs($this->boss)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee($user->login);
    }

    public function testSearchFindsUser(): void
    {
        $user = $this->makeUser();

        $this->actingAs($this->boss)
            ->get('/admin/users/search?q=plain')
            ->assertOk()
            ->assertSee($user->login);
    }

    public function testEditMissingUserReturns404(): void
    {
        $this->actingAs($this->boss)
            ->get('/admin/users/edit?user=nobody')
            ->assertNotFound();
    }

    public function testEditUser(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($this->boss)
            ->post('/admin/users/edit?user=' . $user->login, [
                'level'  => User::USER,
                'email'  => 'changed@example.com',
                'name'   => 'Новое имя',
                'point'  => 100,
                'money'  => 200,
                'themes' => '',
            ]);

        $response->assertRedirect('admin/users/edit?user=' . $user->login);

        $user->refresh();
        $this->assertSame('changed@example.com', $user->email);
        $this->assertSame('Новое имя', $user->name);
        $this->assertSame(100, (int) $user->point);

        $response->assertSessionHas('success');
    }

    public function testEditUserChangesPassword(): void
    {
        $user = $this->makeUser();

        $this->actingAs($this->boss)
            ->post('/admin/users/edit?user=' . $user->login, [
                'level'    => User::USER,
                'email'    => $user->email,
                'password' => 'newsecret123',
                'themes'   => '',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newsecret123', $user->password));
    }

    public function testEditUserCalculatesRating(): void
    {
        $user = $this->makeUser();

        $this->actingAs($this->boss)
            ->post('/admin/users/edit?user=' . $user->login, [
                'level'     => User::USER,
                'email'     => $user->email,
                'posrating' => 10,
                'negrating' => 4,
                'themes'    => '',
            ]);

        $user->refresh();
        $this->assertSame(6, (int) $user->rating);
    }

    public function testEditUserWithInvalidEmailFails(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($this->boss)
            ->post('/admin/users/edit?user=' . $user->login, [
                'level'  => User::USER,
                'email'  => 'не-адрес',
                'themes' => '',
            ]);

        $user->refresh();
        $this->assertSame('plain@example.com', $user->email);

        $response->assertSessionHasErrors('email');
    }

    public function testEditUserWithInvalidLevelFails(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($this->boss)
            ->post('/admin/users/edit?user=' . $user->login, [
                'level'  => 'superhero',
                'email'  => $user->email,
                'themes' => '',
            ]);

        $user->refresh();
        $this->assertSame(User::USER, $user->level);

        $response->assertSessionHasErrors('level');
    }

    public function testDeleteUser(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($this->boss)
            ->post('/admin/users/delete?user=' . $user->login);

        $response->assertRedirect('admin/users');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteUserWithBlacklisting(): void
    {
        $user = $this->makeUser();

        $this->actingAs($this->boss)
            ->post('/admin/users/delete?user=' . $user->login, [
                'loginblack' => 1,
                'mailblack'  => 1,
            ]);

        $this->assertDatabaseHas('blacklist', [
            'type'  => 'login',
            'value' => $user->login,
        ]);

        $this->assertDatabaseHas('blacklist', [
            'type'  => 'email',
            'value' => $user->email,
        ]);
    }

    public function testDeleteAdminIsForbidden(): void
    {
        $admin = User::factory()->admin()->create([
            'login'     => 'other_admin',
            'timebonus' => now(),
        ]);

        $response = $this->actingAs($this->boss)
            ->post('/admin/users/delete?user=' . $admin->login);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $response->assertSessionHasErrors();
    }
}
