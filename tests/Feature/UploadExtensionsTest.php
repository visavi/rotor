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

    /** @var array<int, string> */
    private array $tempFiles = [];

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

        array_map(unlink(...), array_filter($this->tempFiles, is_file(...)));

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

    public function testOwnPickerHidesLink(): void
    {
        // Форма ответа рисует скрепку сама и передаёт id поля — ссылки в списке нет
        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect(), 'attachInputId' => 'reply-attach-1'])->render();

        $this->assertStringContainsString('id="reply-attach-1"', $html);
        $this->assertStringNotContainsString(__('main.attach_files'), $html);

        $html = view('app/_upload_file', ['model' => new Comment(), 'files' => collect()])->render();

        $this->assertStringContainsString(__('main.attach_files'), $html);
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

    public function testRulesRejectForeignExtensionWithImageContent(): void
    {
        // mimes смотрит только на содержимое, а файл сохраняется с расширением клиента:
        // x.html с заголовком GIF лёг бы в uploads страницей со скриптом
        $this->actingAs(User::factory()->create());

        $rules = FileService::rules(Comment::$morphName);

        // Настоящий JPEG со скриптом в сегменте комментария: по содержимому это картинка
        $image = UploadedFile::fake()->image('a.jpg');
        $jpeg = (string) file_get_contents($image->getPathname());
        $script = '<script>alert(1)</script>';
        $polyglot = substr($jpeg, 0, 2) . "\xFF\xFE" . pack('n', strlen($script) + 2) . $script . substr($jpeg, 2);

        // Не fake(): он берёт тип из имени файла, а атака как раз в расхождении имени и содержимого
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $polyglot);

        $html = ['files' => [new UploadedFile($path, 'x.html', null, null, true)]];
        $jpg = ['files' => [new UploadedFile($image->getPathname(), 'photo.jpg', null, null, true)]];

        try {
            $this->assertSame('jpg', $html['files'][0]->guessExtension());
            $this->assertTrue(Validator::make($html, $rules)->fails());
            $this->assertTrue(Validator::make($jpg, $rules)->passes());
        } finally {
            unlink($path);
        }
    }

    public function testFileTypesCheckNameNotContent(): void
    {
        // txt с json или csv внутри — обычный текстовый файл, mimes отклонял его
        // в форме и API, хотя ajax-загрузка принимала
        $this->overrideSetting('file_extensions', 'txt');
        $this->actingAs(User::factory()->create());

        $file = $this->realFile('log.txt', '{"a": 1}');

        $this->assertSame('json', $file->guessExtension());
        $this->assertTrue(Validator::make(['files' => [$file]], FileService::rules(Comment::$morphName))->passes());
    }

    public function testMediaTypesCheckContent(): void
    {
        // Галерея показывает файл картинкой: текст с именем x.jpg не проходит ни формой, ни ajax
        Registry::mediaType(Comment::$morphName);
        $this->actingAs(User::factory()->create());

        $file = $this->realFile('x.jpg', 'не картинка');

        $this->assertTrue(Validator::make(['files' => [$file]], FileService::rules(Comment::$morphName))->fails());

        $result = app(FileService::class)->upload($file, Comment::$morphName, 0);

        $this->assertFalse($result['success']);
        $this->assertSame(__('validator.extension'), $result['message']);
    }

    public function testPendingWithoutUserFindsNothing(): void
    {
        // Без пользователя scope не падает TypeError, а ничего не находит
        $this->assertSame(0, File::query()->pending(Comment::$morphName, null)->count());
    }

    /**
     * Файл с настоящим содержимым: fake() берёт тип из имени, а проверяется как раз содержимое
     */
    private function realFile(string $name, string $content): UploadedFile
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }

    private function createPending(User $user, int $count): void
    {
        foreach (range(1, $count) as $i) {
            File::factory()->create([
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
