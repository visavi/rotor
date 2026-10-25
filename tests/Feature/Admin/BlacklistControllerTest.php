<?php

namespace Tests\Feature\Admin;

use App\Models\BlackList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlacklistControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->admin = User::factory()->admin()->create(['login' => 'admin_blacklist']);
    }

    public function testIndexShowsRecordsOfType(): void
    {
        BlackList::factory()->create([
            'type'       => 'email',
            'value'      => 'spam@example.com',
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/blacklists?type=email')
            ->assertOk()
            ->assertSee('spam@example.com');
    }

    public function testIndexWithUnknownTypeReturns404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/blacklists?type=phone')
            ->assertNotFound();
    }

    public function testIndexWithArrayTypeReturns404(): void
    {
        // Массив вместо строки в адресе раньше ронял страницу с 500
        $this->actingAs($this->admin)
            ->get('/admin/blacklists?type[]=email')
            ->assertNotFound();
    }

    public function testAddEmail(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=email', ['value' => 'spam@example.com']);

        $response->assertRedirect('admin/blacklists?type=email');

        $this->assertDatabaseHas('blacklist', [
            'type'    => 'email',
            'value'   => 'spam@example.com',
            'user_id' => $this->admin->id,
        ]);

        $response->assertSessionHas('success');
    }

    public function testAddInvalidEmailFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=email', ['value' => 'not-an-email']);

        $this->assertDatabaseMissing('blacklist', ['value' => 'not-an-email']);

        $response->assertSessionHasErrors('value');
    }

    public function testAddLoginIsLowercased(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=login', ['value' => 'SpamUser']);

        // Сравниваем в PHP: у таблицы case-insensitive collation
        $this->assertSame(
            'spamuser',
            BlackList::query()->where('type', 'login')->value('value'),
        );
    }

    public function testAddLoginWithInvalidCharsFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=login', ['value' => 'спам юзер']);

        $this->assertDatabaseCount('blacklist', 1); // только сидерная domain-запись

        $response->assertSessionHasErrors(['value' => __('admin.blacklists.invalid_login')]);
    }

    public function testAddDomainIsNormalizedToHost(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=domain', ['value' => 'https://Spam.Example.COM/path?a=1']);

        $this->assertDatabaseHas('blacklist', [
            'type'  => 'domain',
            'value' => 'spam.example.com',
        ]);
    }

    public function testAddDomainWithoutSchemeIsNormalized(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=domain', ['value' => 'spam.example.com/path']);

        $this->assertDatabaseHas('blacklist', [
            'type'  => 'domain',
            'value' => 'spam.example.com',
        ]);
    }

    public function testAddDuplicateFails(): void
    {
        BlackList::factory()->create([
            'type'       => 'email',
            'value'      => 'spam@example.com',
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=email', ['value' => 'spam@example.com']);

        $this->assertDatabaseCount('blacklist', 2); // сидерная domain + созданная выше

        $response->assertSessionHasErrors(['value' => __('main.record_exists')]);
    }

    public function testAddDuplicateDomainFailsAfterNormalization(): void
    {
        BlackList::factory()->create([
            'type'       => 'domain',
            'value'      => 'spam.example.com',
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);

        $before = BlackList::query()->where('type', 'domain')->count();

        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=domain', ['value' => 'HTTPS://Spam.Example.com/page']);

        $response->assertSessionHasErrors(['value' => __('main.record_exists')]);

        $this->assertSame($before, BlackList::query()->where('type', 'domain')->count());
    }

    public function testAddShowsErrorOnFormAfterRedirect(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/blacklists?type=email')
            ->post('/admin/blacklists?type=email', ['value' => 'not-an-email']);

        $response->assertRedirect('/admin/blacklists?type=email');

        $this->actingAs($this->admin)
            ->get('/admin/blacklists?type=email')
            ->assertOk()
            ->assertSee(__('validator.email'))
            ->assertSee('is-invalid')
            ->assertSee('value="not-an-email"', false);
    }

    public function testDeleteRecords(): void
    {
        $record = BlackList::factory()->create([
            'type'       => 'email',
            'value'      => 'spam@example.com',
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists/delete?type=email', ['del' => [$record->id]]);

        $response->assertRedirect('admin/blacklists?type=email&page=1');

        $this->assertDatabaseMissing('blacklist', ['id' => $record->id]);

        $response->assertSessionHas('success');
    }

    public function testDeleteWithoutSelectionFails(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists/delete?type=email', []);

        $this->assertDatabaseCount('blacklist', 1);

        $response->assertSessionHasErrors();
    }

    public function testDeleteDoesNotTouchOtherType(): void
    {
        $email = BlackList::factory()->create([
            'type'       => 'email',
            'value'      => 'spam@example.com',
            'user_id'    => $this->admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post('/admin/blacklists/delete?type=login', ['del' => [$email->id]]);

        $this->assertDatabaseHas('blacklist', ['id' => $email->id]);
    }

    public function testAddEmptyDomainFails(): void
    {
        // Пустое поле: parse_url() отдаёт false, раньше это был TypeError и 500
        $before = BlackList::query()->where('type', 'domain')->count();

        $response = $this->actingAs($this->admin)
            ->post('/admin/blacklists?type=domain', ['value' => '']);

        $response->assertRedirect();
        $response->assertSessionHasErrors('value');

        $this->assertSame($before, BlackList::query()->where('type', 'domain')->count());
    }
}
