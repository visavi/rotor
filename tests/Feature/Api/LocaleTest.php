<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('language', 'ru');
    }

    public function testGuestGetsSiteLanguage(): void
    {
        app()->setLocale('en');

        $this->getJson('/api/config')->assertOk();

        $this->assertSame('ru', app()->getLocale());
    }

    public function testAcceptLanguageHeaderIsHonored(): void
    {
        $this->getJson('/api/config', ['Accept-Language' => 'en-US,en;q=0.9'])->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function testUnknownHeaderLanguageFallsBackToSite(): void
    {
        app()->setLocale('en');

        $this->getJson('/api/config', ['Accept-Language' => 'de'])->assertOk();

        $this->assertSame('ru', app()->getLocale());
    }

    public function testWebGuestGetsBrowserLanguage(): void
    {
        $this->get('/', ['Accept-Language' => 'en'])->assertOk();

        $this->assertSame('en', app()->getLocale());
    }

    public function testSessionChoiceBeatsHeader(): void
    {
        $this->withSession(['language' => 'ua'])
            ->get('/', ['Accept-Language' => 'en'])
            ->assertOk();

        $this->assertSame('ua', app()->getLocale());
    }

    public function testProfileLanguageBeatsHeader(): void
    {
        $user = User::factory()->create(['apikey' => Str::random(32), 'language' => 'ua']);

        $this->getJson('/api/feed', [
            'Authorization'   => 'Bearer ' . $user->apikey,
            'Accept-Language' => 'en',
        ])->assertOk();

        $this->assertSame('ua', app()->getLocale());
    }
}
