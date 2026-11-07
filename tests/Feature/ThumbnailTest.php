<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Services\ThumbnailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileSystem;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Tests\TestCase;

/**
 * Превью картинок: создаётся при первом выводе картинки,
 * дальше страница ссылается на готовый файл
 */
class ThumbnailTest extends TestCase
{
    use RefreshDatabase;

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicPath = storage_path('framework/testing/public-' . uniqid());
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        FileSystem::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function testFirstOutputCreatesScaledThumbnail(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);

        $this->assertSame('/uploads/thumbnails/700/forums/big.webp', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertSame([700, 467, 'image/webp'], $this->imageSize('uploads/thumbnails/700/forums/big.webp'));
        $this->assertSame([1500, 1000, 'image/jpeg'], $this->imageSize('uploads/forums/big.jpg'));
    }

    public function testExistingThumbnailIsNotRebuilt(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);
        $this->url('/uploads/forums/big.jpg', 700);

        $thumbnail = public_path('uploads/thumbnails/700/forums/big.webp');
        file_put_contents($thumbnail, 'cached');

        $this->assertSame('/uploads/thumbnails/700/forums/big.webp', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertStringEqualsFile($thumbnail, 'cached');

        // Превью в формате оригинала, созданное до перехода на webp, тоже годится
        $this->putImage('forums/old.jpg', 1500, 1000);
        FileSystem::put(public_path('uploads/thumbnails/700/forums/old.jpg'), 'old');

        $this->assertSame('/uploads/thumbnails/700/forums/old.jpg', $this->url('/uploads/forums/old.jpg', 700));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/old.webp'));
    }

    public function testSquareThumbnailIsCropped(): void
    {
        $this->putImage('photos/wide.png', 1200, 400);

        $this->url('/uploads/photos/wide.png', 150);

        $this->assertSame([150, 150, 'image/webp'], $this->imageSize('uploads/thumbnails/150/photos/wide.webp'));
    }

    public function testSmallImageKeepsSizeButBecomesWebp(): void
    {
        $this->putImage('forums/small.jpg', 300, 200);

        $this->url('/uploads/forums/small.jpg', 700);

        $this->assertSame([300, 200, 'image/webp'], $this->imageSize('uploads/thumbnails/700/forums/small.webp'));
    }

    public function testSmallWebpIsCopiedAsIs(): void
    {
        $this->putImage('forums/small.webp', 300, 200);

        $this->assertSame('/uploads/thumbnails/700/forums/small.webp', $this->url('/uploads/forums/small.webp', 700));
        $this->assertFileEquals(public_path('uploads/forums/small.webp'), public_path('uploads/thumbnails/700/forums/small.webp'));
    }

    public function testKeepsOriginalFormatWhenWebpIsHeavier(): void
    {
        // Скриншот текста: png сжимает его в разы лучше, чем webp с потерями
        $im = imagecreatetruecolor(300, 200);
        // В truecolor-картинке цвет — просто 0xRRGGBB
        imagefill($im, 0, 0, 0xFFFFFF);

        for ($y = 5; $y < 200; $y += 12) {
            imagestring($im, 3, 5, $y, str_repeat('Lorem ipsum dolor ', 3), 0x000000);
        }

        FileSystem::ensureDirectoryExists(public_path('uploads/forums'));
        imagepng($im, public_path('uploads/forums/text.png'));

        $this->assertSame('/uploads/thumbnails/700/forums/text.png', $this->url('/uploads/forums/text.png', 700));
        $this->assertFileEquals(public_path('uploads/forums/text.png'), public_path('uploads/thumbnails/700/forums/text.png'));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/text.webp'));

        // Готовое превью в формате оригинала находится сразу, без новой попытки
        $this->assertSame('/uploads/thumbnails/700/forums/text.png', $this->url('/uploads/forums/text.png', 700));
    }

    public function testWithoutWebpSupportThumbnailKeepsOriginalFormat(): void
    {
        $this->app->instance(ImageManager::class, new ImageManager(new class extends GdDriver {
            public function supports(mixed $identifier): bool
            {
                return Format::tryCreate($identifier) !== Format::WEBP && parent::supports($identifier);
            }
        }));
        $this->putImage('forums/big.jpg', 1500, 1000);
        $this->putImage('forums/small.jpg', 300, 200);

        $this->assertSame('/uploads/thumbnails/700/forums/big.jpg', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertSame([700, 467, 'image/jpeg'], $this->imageSize('uploads/thumbnails/700/forums/big.jpg'));

        // Тот же формат — маленькую картинку снова можно просто скопировать
        $this->url('/uploads/forums/small.jpg', 700);
        $this->assertFileEquals(public_path('uploads/forums/small.jpg'), public_path('uploads/thumbnails/700/forums/small.jpg'));

        // Удаляется превью под любым из двух имён
        FileSystem::put(public_path('uploads/thumbnails/700/forums/big.webp'), 'old');
        ThumbnailService::delete('/uploads/forums/big.jpg');
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/big.jpg'));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/big.webp'));
    }

    public function testKeepsOriginalWhenThumbnailIsNotPossible(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);
        $this->putImage('stickers/anim.gif', 1500, 1000);
        FileSystem::put(public_path('uploads/forums/broken.jpg'), 'not an image');

        // Анимация не переживает пересохранение, svg и так лёгкий
        $this->assertSame('/uploads/stickers/anim.gif', $this->url('/uploads/stickers/anim.gif', 700));
        $this->assertSame('/uploads/files/a.svg', $this->url('/uploads/files/a.svg', 700));
        $this->assertSame('/assets/img/a.jpg', $this->url('/assets/img/a.jpg', 700));
        $this->assertSame('/uploads/forums/big.jpg', $this->url('/uploads/forums/big.jpg', 500));
        $this->assertSame('/uploads/forums/missing.jpg', $this->url('/uploads/forums/missing.jpg', 700));
        $this->assertSame('/uploads/forums/broken.jpg', $this->url('/uploads/forums/broken.jpg', 700));
        $this->assertSame('/uploads/../forums/big.jpg', $this->url('/uploads/../forums/big.jpg', 700));

        $this->assertDirectoryDoesNotExist(public_path('uploads/thumbnails/700/stickers'));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/broken.webp'));
    }

    public function testFailureKeepsOriginalAndLeavesNoTempFiles(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);
        // Каталог превью не создать: на его месте файл, как при нехватке прав
        FileSystem::ensureDirectoryExists(public_path('uploads/thumbnails'));
        FileSystem::put(public_path('uploads/thumbnails/700'), '');

        $this->assertSame('/uploads/forums/big.jpg', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertSame(['700'], array_values(array_diff(scandir(public_path('uploads/thumbnails')), ['.', '..'])));
    }

    public function testRequestCreatesLimitedNumberOfThumbnails(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->putImage("forums/{$i}.jpg", 300, 200);
        }

        $urls = array_map(fn (int $i) => $this->url("/uploads/forums/{$i}.jpg", 700), range(1, 11));

        $this->assertSame('/uploads/thumbnails/700/forums/10.webp', $urls[9]);
        $this->assertSame('/uploads/forums/11.jpg', $urls[10]);

        // Готовые превью лимит не тратят
        $this->assertSame('/uploads/thumbnails/700/forums/1.webp', $this->url('/uploads/forums/1.jpg', 700));
    }

    public function testDeletingFileRemovesItsThumbnails(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);

        $file = File::query()->create([
            'relate_id'   => 1,
            'relate_type' => 'posts',
            'path'        => '/uploads/forums/big.jpg',
            'name'        => 'big.jpg',
            'size'        => 1,
            'extension'   => 'jpg',
            'mime_type'   => 'image/jpeg',
            'user_id'     => User::factory()->create()->id,
            'sort'        => 1,
        ]);

        $file->thumbnail(ThumbnailService::MEDIA);
        $file->thumbnail(ThumbnailService::SQUARE);
        $this->assertFileExists(public_path('uploads/thumbnails/700/forums/big.webp'));
        $this->assertFileExists(public_path('uploads/thumbnails/150/forums/big.webp'));

        $file->delete();

        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/big.webp'));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/150/forums/big.webp'));
    }

    public function testClearRemovesAllThumbnailsAndKeepsOriginals(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);
        $this->url('/uploads/forums/big.jpg', 700);
        $this->url('/uploads/forums/big.jpg', 150);

        // Брошенный временный файл тоже считается и удаляется
        file_put_contents(public_path('uploads/thumbnails/700/forums/.tmp.jpg'), 'xx');

        $stats = ThumbnailService::stats();
        $this->assertSame(['150', '700'], array_map('strval', array_keys($stats)));
        $this->assertSame(2, $stats[700]['count']);

        $this->assertTrue(ThumbnailService::clear());

        $this->assertDirectoryDoesNotExist(public_path('uploads/thumbnails'));
        $this->assertFileExists(public_path('uploads/forums/big.jpg'));

        // Чистить нечего — тоже успех
        $this->assertTrue(ThumbnailService::clear());
        $this->assertSame([], ThumbnailService::stats());

        // После очистки превью создаётся заново
        $this->assertSame('/uploads/thumbnails/700/forums/big.webp', $this->url('/uploads/forums/big.jpg', 700));
    }

    private function url(string $path, int $size): string
    {
        return ThumbnailService::url($path, $size);
    }

    private function putImage(string $path, int $width, int $height): void
    {
        $target = public_path('uploads/' . $path);
        FileSystem::ensureDirectoryExists(dirname($target));

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        // Временный файл живёт, пока жив объект: без переменной он исчезает до копирования
        $image = UploadedFile::fake()->image('source.' . $extension, $width, $height);
        FileSystem::copy($image->getPathname(), $target);
    }

    /**
     * @return array{int, int, string} Ширина, высота и mime
     */
    private function imageSize(string $path): array
    {
        $size = getimagesize(public_path($path));
        $this->assertIsArray($size, "Не картинка: {$path}");

        return [$size[0], $size[1], $size['mime']];
    }
}
