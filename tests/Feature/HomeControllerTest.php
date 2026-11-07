<?php

namespace Tests\Feature;

use App\Models\Ban;
use App\Models\Error;
use App\Models\User;
use App\Support\Hook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('captcha_type', 'disable');
    }

    public function testSearchPageIsShown(): void
    {
        $this->get('/search')->assertOk();
    }

    public function testArrayParametersFallBackToDefaults(): void
    {
        // ?sort[]= приходит массивом: типизированные параметры падали с TypeError и страница отдавала 500
        $this->get('/search?query=тест&sort[]=x&type[]=y')->assertOk();
        $this->get('/search?query[]=x')->assertOk();
        $this->getJson('/api/search?query[]=x')->assertStatus(422);
        $this->get('/users?sort[]=x&order[]=y&type[]=z&user[]=u')->assertOk();
        $this->get('/feed?page[]=2')->assertRedirect('/?page=1');
        $this->getJson('/search-users?query[]=x')->assertOk();
    }

    public function testSearchWithShortQueryShowsError(): void
    {
        $response = $this->get('/search?query=ab');

        $response->assertOk();
        $response->assertSee(__('main.request_length'));

        // Сообщение — только для этого ответа, на следующую страницу не переходит
        $this->get('/search')->assertDontSee(__('main.request_length'));
    }

    public function testIpbanPageForBannedIp(): void
    {
        Ban::factory()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => 0,
            'created_at' => now()->subHour(),
        ]);

        $this->get('/ipban')->assertStatus(429);
    }

    public function testIpbanPageHasNoAdvertsOrFooter(): void
    {
        // Хуки живут в статике весь процесс: свои убрать, чужие вернуть
        $hooks = Hook::getHooks();
        Hook::flush();
        Hook::add('advertTop', static fn () => '<b>advert-top</b>');
        Hook::add('advertBottom', static fn () => '<b>advert-bottom</b>');

        try {
            // Реклама гасится секциями макета темы: каждая встроенная тема должна их объявлять
            // и гасить футер с копирайтом, даже если копирайт стоит прямо в макете
            $this->overrideSetting('copy', 'site-copyright');

            foreach (glob(resource_path('views/themes/*'), GLOB_ONLYDIR) ?: [] as $dir) {
                $this->overrideSetting('themes', basename($dir));

                $this->get('/search')->assertSee('advert-top')->assertSee('advert-bottom')->assertSee('site-copyright');

                $ban = Ban::factory()->create(['ip' => '127.0.0.1', 'user_id' => 0, 'created_at' => now()->subHour()]);

                $this->get('/ipban')
                    ->assertStatus(429)
                    ->assertDontSee('advert-top')
                    ->assertDontSee('advert-bottom')
                    ->assertDontSee('site-copyright');

                $ban->delete();
                clearCache('ipBan');
            }
        } finally {
            Hook::flush();

            foreach ($hooks as $name => $items) {
                foreach ($items as $hook) {
                    Hook::add($name, $hook['value'], $hook['priority']);
                }
            }
        }
    }

    public function testIpbanRedirectsWhenNotBanned(): void
    {
        $this->get('/ipban')->assertRedirect('/');
    }

    public function testIpbanSelfUnbanWithCaptcha(): void
    {
        $ban = Ban::factory()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => 0,
            'created_at' => now()->subHour(),
        ]);

        $response = $this->post('/ipban');

        $response->assertRedirect('/');

        $this->assertDatabaseMissing('ban', ['id' => $ban->id]);

        $response->assertSessionHas('success');
    }

    public function testIpbanSelfUnbanMarksLatestAutobanLog(): void
    {
        Ban::factory()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => 0,
            'created_at' => now()->subHour(),
        ]);

        $old = Error::factory()->create([
            'code'       => 666,
            'ip'         => '127.0.0.1',
            'brow'       => 'Test',
            'created_at' => now()->subDay(),
        ]);

        $latest = Error::factory()->create([
            'code'       => 666,
            'ip'         => '127.0.0.1',
            'brow'       => 'Test',
            'created_at' => now()->subHour(),
        ]);

        $this->post('/ipban')->assertRedirect('/');

        $this->assertSame(
            __('pages.ip_unbanned_log', ['date' => dateFixed(now(), original: true)]),
            $latest->fresh()?->message,
        );

        $this->assertNull($old->fresh()?->message);
    }

    public function testIpbanCannotSelfUnbanWhenBannedByAdmin(): void
    {
        $admin = User::factory()->admin()->create([
            'login'     => 'admin_ipban',
            'timebonus' => now(),
        ]);

        $ban = Ban::factory()->create([
            'ip'         => '127.0.0.1',
            'user_id'    => $admin->id,
            'created_at' => now()->subHour(),
        ]);

        $this->post('/ipban')->assertStatus(429);

        $this->assertDatabaseHas('ban', ['id' => $ban->id]);
    }
}
