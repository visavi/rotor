<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Services\ThumbnailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File as FileSystem;
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

        $this->assertSame('/uploads/thumbnails/700/forums/big.jpg', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertSame([700, 467], $this->imageSize('uploads/thumbnails/700/forums/big.jpg'));
        $this->assertSame([1500, 1000], $this->imageSize('uploads/forums/big.jpg'));
    }

    public function testExistingThumbnailIsNotRebuilt(): void
    {
        $this->putImage('forums/big.jpg', 1500, 1000);
        $this->url('/uploads/forums/big.jpg', 700);

        $thumbnail = public_path('uploads/thumbnails/700/forums/big.jpg');
        file_put_contents($thumbnail, 'cached');

        $this->assertSame('/uploads/thumbnails/700/forums/big.jpg', $this->url('/uploads/forums/big.jpg', 700));
        $this->assertStringEqualsFile($thumbnail, 'cached');
    }

    public function testSquareThumbnailIsCropped(): void
    {
        $this->putImage('photos/wide.png', 1200, 400);

        $this->url('/uploads/photos/wide.png', 150);

        $this->assertSame([150, 150], $this->imageSize('uploads/thumbnails/150/photos/wide.png'));
    }

    public function testSmallImageIsCopiedAsIs(): void
    {
        $this->putImage('forums/small.jpg', 300, 200);

        $this->url('/uploads/forums/small.jpg', 700);

        $this->assertFileEquals(public_path('uploads/forums/small.jpg'), public_path('uploads/thumbnails/700/forums/small.jpg'));
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
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/broken.jpg'));
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

        $this->assertSame('/uploads/thumbnails/700/forums/10.jpg', $urls[9]);
        $this->assertSame('/uploads/forums/11.jpg', $urls[10]);

        // Готовые превью лимит не тратят
        $this->assertSame('/uploads/thumbnails/700/forums/1.jpg', $this->url('/uploads/forums/1.jpg', 700));
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
        $this->assertFileExists(public_path('uploads/thumbnails/700/forums/big.jpg'));

        $file->delete();

        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/700/forums/big.jpg'));
        $this->assertFileDoesNotExist(public_path('uploads/thumbnails/150/forums/big.jpg'));
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
     * @return array{int, int}
     */
    private function imageSize(string $path): array
    {
        $size = getimagesize(public_path($path));
        $this->assertIsArray($size, "Не картинка: {$path}");

        return [$size[0], $size[1]];
    }
}
