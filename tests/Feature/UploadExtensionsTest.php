<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UploadExtensionsTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $mediaTypes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('file_extensions', 'zip,pdf,jpg');
        $this->overrideSetting('media_extensions', 'jpg,mp4');

        // Реестр живёт в статике весь процесс: типы теста иначе протекают в соседние
        $this->mediaTypes = Registry::$mediaTypes;
    }

    protected function tearDown(): void
    {
        Registry::$mediaTypes = $this->mediaTypes;

        parent::tearDown();
    }

    public function testFileFormOffersFileExtensions(): void
    {
        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringContainsString('accept=".zip,.pdf,.jpg"', $html);
    }

    public function testFileFormOfMediaTypeOffersMediaExtensions(): void
    {
        // Гостевая и новости принимают медиа, но подключают форму файлов:
        // список в ней должен совпадать с тем, что пропустит сервер
        Registry::mediaType(Comment::$morphName);

        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringContainsString('accept=".jpg,.mp4"', $html);
        $this->assertStringNotContainsString('.pdf', $html);
    }
}
