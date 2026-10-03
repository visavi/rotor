<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use App\Support\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
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
        // Форма берёт список по типу записи: подключённая к медиа-типу,
        // она не должна предлагать то, что сервер отклонит
        Registry::mediaType(Comment::$morphName);

        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringContainsString('accept=".jpg,.mp4"', $html);
        $this->assertStringNotContainsString('.pdf', $html);
    }

    public function testMediaFormOffersMediaExtensions(): void
    {
        Registry::mediaType(Comment::$morphName);

        $html = view('app/_upload_media', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringContainsString('accept=".jpg,.mp4"', $html);
        $this->assertStringNotContainsString('image/*', $html);
    }

    public function testExtensionsAreNormalized(): void
    {
        // Validator::file сравнивает расширение файла в нижнем регистре
        $this->overrideSetting('file_extensions', ' PDF, Zip ,,jpg');

        $this->assertSame(['pdf', 'zip', 'jpg'], FileService::extensions(Comment::$morphName));
    }

    public function testEmptyListOmitsAccept(): void
    {
        // Пустой accept браузер понимает как «любые файлы»
        $this->overrideSetting('file_extensions', '');

        $this->assertNull(FileService::accept(Comment::$morphName));

        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringNotContainsString('accept=', $html);
    }

    public function testRulesCountPendingFiles(): void
    {
        // Файлы, загруженные заранее, тоже лягут в запись — лимит общий
        $this->overrideSetting('maxfiles', 3);
        $this->overrideSetting('file_extensions', 'pdf');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->createPending($user, 2);

        $rules = FileService::rules(Comment::$morphName);

        $one = ['files' => [UploadedFile::fake()->create('a.pdf', 1, 'application/pdf')]];
        $two = ['files' => [
            UploadedFile::fake()->create('a.pdf', 1, 'application/pdf'),
            UploadedFile::fake()->create('b.pdf', 1, 'application/pdf'),
        ]];

        $this->assertTrue(Validator::make($one, $rules)->passes());

        $errors = Validator::make($two, $rules)->errors();
        $this->assertSame(__('validator.files_max', ['max' => 3]), $errors->first('files'));
    }

    public function testPendingWithoutUserFindsNothing(): void
    {
        // Без пользователя scope не падает TypeError, а ничего не находит
        $this->assertSame(0, File::query()->pending(Comment::$morphName, null)->count());
    }

    private function createPending(User $user, int $count): void
    {
        foreach (range(1, $count) as $i) {
            File::query()->create([
                'relate_id'   => 0,
                'relate_type' => Comment::$morphName,
                'path'        => '/uploads/comments/' . $i . '.pdf',
                'name'        => $i . '.pdf',
                'size'        => 1024,
                'extension'   => 'pdf',
                'mime_type'   => 'application/pdf',
                'user_id'     => $user->id,
                'sort'        => $i,
            ]);
        }
    }
}
