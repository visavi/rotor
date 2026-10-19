<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UploadDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Каталоги загрузок не в репозитории: проверяем в пустом public, где их ещё нет
        $this->publicPath = storage_path('framework/testing/public-' . uniqid());
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function testResizedImageCreatesMissingDirectory(): void
    {
        $this->overrideSetting('screensize', 100);
        $this->overrideSetting('copyfoto', 0);

        $upload = (new Comment())->uploadFile(UploadedFile::fake()->image('big.jpg', 300, 300), false);

        $this->assertFileExists(public_path($upload['path']));
        $this->assertStringStartsWith('/uploads/comments/', $upload['path']);
    }

    public function testPlainFileCreatesMissingDirectory(): void
    {
        $upload = (new Comment())->uploadFile(UploadedFile::fake()->create('doc.txt', 1), false);

        $this->assertFileExists(public_path($upload['path']));
    }

    public function testProfilePhotoCreatesMissingAvatarDirectory(): void
    {
        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('copyfoto', 0);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/pictures', ['photo' => UploadedFile::fake()->image('me.jpg', 300, 300)])
            ->assertRedirect('profile');

        $user->refresh();
        $avatar = public_path($user->avatar);
        $picture = public_path($user->picture);
        $this->assertFileExists($avatar);
        $this->assertFileExists($picture);

        $this->delete('/pictures/delete')->assertRedirect('profile');

        $this->assertFileDoesNotExist($avatar);
        $this->assertFileDoesNotExist($picture);
        $this->assertNull($user->fresh()->picture);
        $this->assertNull($user->fresh()->avatar);
    }
}
