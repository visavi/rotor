<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileSystem;
use Tests\TestCase;

/**
 * Загрузка картинок слайдера: первая на странице — обычно LCP, её браузер
 * должен грузить сразу и с высоким приоритетом. Остальные — лениво, иначе
 * на мобильном они делят канал с первой и оттягивают отрисовку
 */
class SliderLoadingTest extends TestCase
{
    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Слайдер создаёт превью: не трогать настоящий public/uploads
        $this->publicPath = storage_path('framework/testing/public-' . uniqid());
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        FileSystem::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function testOnlyFirstImageOnPageLoadsEagerly(): void
    {
        $first = $this->renderSlider(1, ['a.jpg', 'b.jpg']);
        $second = $this->renderSlider(2, ['c.jpg']);

        $this->assertSame(['high', null], $this->loading($first, 'slide-main-img'));
        $this->assertSame([null, 'lazy'], $this->loading($second, 'slide-main-img'));

        // Миниатюры не бывают LCP, даже в первом слайдере
        $this->assertSame([null, 'lazy'], $this->loading($first, 'slide-thumb-image', 0));
        $this->assertSame([null, 'lazy'], $this->loading($first, 'slide-thumb-image', 1));
    }

    public function testSliderShowsThumbnailsAndLinksToOriginal(): void
    {
        FileSystem::ensureDirectoryExists(public_path('uploads'));
        $image = UploadedFile::fake()->image('a.jpg', 1500, 1000);
        FileSystem::copy($image->getPathname(), public_path('uploads/a.jpg'));

        $html = $this->renderSlider(1, ['a.jpg', 'b.gif']);

        // Кадр — превью по ширине блока, миниатюры — квадрат, галерея открывает оригинал
        $this->assertStringContainsString('<a href="/uploads/a.jpg" class="slide-main-link"', $html);
        $this->assertStringContainsString('<img src="/uploads/thumbnails/700/a.webp"', $html);
        $this->assertStringContainsString('data-preview="/uploads/thumbnails/700/a.webp"', $html);
        $this->assertStringContainsString('<img src="/uploads/thumbnails/150/a.webp"', $html);

        // Gif без превью: анимация не переживает пересохранение
        $this->assertStringContainsString('<img src="/uploads/b.gif"', $html);
        $this->assertStringNotContainsString('thumbnails/150/b.gif', $html);
    }

    public function testVideoDoesNotTakeImagePriority(): void
    {
        $this->renderSlider(1, ['clip.mp4']);
        $image = $this->renderSlider(2, ['a.jpg']);

        $this->assertSame(['high', null], $this->loading($image, 'slide-main-img'));
    }

    /**
     * Атрибуты fetchpriority и loading у n-й картинки с классом
     *
     * @return array{?string, ?string}
     */
    private function loading(string $html, string $class, int $index = 0): array
    {
        preg_match_all('/<img [^>]*class="[^"]*\b' . $class . '\b[^>]*>/', $html, $matches);
        $tag = $matches[0][$index] ?? '';

        $this->assertNotSame('', $tag, "Нет картинки .{$class} #{$index}");

        $attr = static fn (string $name) => preg_match('/\s' . $name . '="([^"]*)"/', $tag, $m) ? $m[1] : null;

        return [$attr('fetchpriority'), $attr('loading')];
    }

    /**
     * @param list<string> $names
     */
    private function renderSlider(int $id, array $names): string
    {
        $files = collect($names)->map(static fn (string $name) => new File([
            'path'      => '/uploads/' . $name,
            'name'      => $name,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
        ]));

        return view('app/_slider', [
            'model'      => (new User())->forceFill(['id' => $id]),
            'files'      => $files,
            'countFiles' => $files->count(),
        ])->render();
    }
}
