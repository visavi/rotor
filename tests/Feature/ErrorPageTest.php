<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        // У 410 своего шаблона нет — уходит в errors.default
        Route::get('/test-gone', static fn () => throw new HttpException(410, '', null, ['X-Test' => 'gone']));
        Route::post('/test-token', static fn () => throw new TokenMismatchException());
        Route::get('/test-notice', static fn () => abort(200, 'Разделы еще не созданы!'));
    }

    public function testAbortWithOkIsNoticeNotError(): void
    {
        // abort(200, ...) — уведомление, а не ошибка: страница с текстом и код 200
        $this->get('/test-notice')
            ->assertOk()
            ->assertSee('Разделы еще не созданы!');

        $this->getJson('/test-notice')
            ->assertOk()
            ->assertExactJson(['message' => 'Разделы еще не созданы!']);

        $this->assertDatabaseCount('errors', 0);
    }

    public function testErrorWithoutTemplateKeepsStatusAndHeaders(): void
    {
        $this->get('/test-gone')
            ->assertStatus(410)
            ->assertHeader('X-Test', 'gone');
    }

    public function testErrorWithTemplateKeepsStatus(): void
    {
        // Картинка есть только в шаблоне сайта, а не в запасной странице Laravel
        $this->get('/missing-page-for-test')
            ->assertNotFound()
            ->assertSee('/assets/img/errors/', false);
    }

    public function testThemeOverridesErrorTemplate(): void
    {
        // Каталоги переопределений темы создаются на время теста и убираются за собой
        $viewsDir = resource_path('views/themes/default/views');
        $created = array_values(array_filter([$viewsDir, $viewsDir . '/errors'], static fn ($dir) => ! is_dir($dir)));
        $file = $viewsDir . '/errors/410.blade.php';

        foreach ($created as $dir) {
            mkdir($dir, 0755);
        }

        file_put_contents($file, 'theme-410-page');

        try {
            $this->get('/test-gone')
                ->assertStatus(410)
                ->assertSee('theme-410-page');
        } finally {
            unlink($file);

            foreach (array_reverse($created) as $dir) {
                rmdir($dir);
            }
        }
    }

    public function testAjaxWithoutAcceptGetsJson(): void
    {
        // Подгрузка ленты шлёт Accept: */* — страница ошибки не должна попасть в ленту
        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => '*/*'])
            ->get('/test-gone')
            ->assertStatus(410)
            ->assertExactJson(['message' => __('errors.error')]);
    }

    public function testExpiredTokenRedirectsBackWithError(): void
    {
        $this->from('/forums')
            ->post('/test-token')
            ->assertRedirect('/forums')
            ->assertSessionHasErrors(['token' => __('validator.token')]);
    }

    public function testExpiredTokenInJsonIs419(): void
    {
        $this->postJson('/test-token')
            ->assertStatus(419)
            ->assertExactJson(['message' => __('validator.token')]);
    }

    public function testJsonErrorKeepsHeaders(): void
    {
        $this->getJson('/test-gone')
            ->assertStatus(410)
            ->assertHeader('X-Test', 'gone')
            ->assertExactJson(['message' => __('errors.error')]);
    }
}
