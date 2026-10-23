<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);
        $this->overrideSetting('maxfiles', 5);
        $this->overrideSetting('filesize', 10000);
        $this->overrideSetting('screensize', 1000);
        $this->overrideSetting('file_extensions', 'jpg,png,zip,txt');
        $this->overrideSetting('media_extensions', 'jpg,png');

        $this->user = User::factory()->create(['apikey' => Str::random(32)]);
    }

    public function testUploadRequiresToken(): void
    {
        $this->postJson('/api/files', ['type' => Comment::$morphName])
            ->assertStatus(400);
    }

    public function testUploadAndListAndDelete(): void
    {
        $response = $this->post(
            '/api/files',
            [
                'type' => Comment::$morphName,
                'id'   => 0,
                'file' => UploadedFile::fake()->image('screen.jpg', 300, 300),
            ],
            $this->headers(),
        );

        $response->assertStatus(201)
            ->assertJsonPath('file.is_image', true)
            ->assertJsonStructure(['message', 'file' => ['id', 'name', 'path', 'size']]);

        $fileId = $response->json('file.id');

        // Файл ждет привязки к будущему комментарию
        $this->assertDatabaseHas('files', [
            'id'          => $fileId,
            'relate_type' => Comment::$morphName,
            'relate_id'   => 0,
            'user_id'     => $this->user->id,
        ]);

        $this->getJson('/api/files?type=' . Comment::$morphName, $this->headers())
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $fileId);

        $this->deleteJson('/api/files/' . $fileId, ['type' => Comment::$morphName], $this->headers())
            ->assertOk();

        $this->assertDatabaseMissing('files', ['id' => $fileId]);
    }

    public function testWrongExtensionIsRejected(): void
    {
        $this->upload(UploadedFile::fake()->create('virus.exe', 1))
            ->assertStatus(422)
            ->assertJsonPath('message', __('validator.extension'));
    }

    public function testSmallImageIsRejected(): void
    {
        $this->upload(UploadedFile::fake()->image('tiny.jpg', 50, 50))
            ->assertStatus(422)
            ->assertJsonPath('message', __('validator.weight_min', ['weight' => 100]));
    }

    public function testDuplicateNameIsRejected(): void
    {
        $this->upload(UploadedFile::fake()->image('screen.jpg', 300, 300))->assertStatus(201);

        $this->upload(UploadedFile::fake()->image('screen.jpg', 300, 300))
            ->assertStatus(422)
            ->assertJsonPath('message', __('validator.file_duplicate'));
    }

    public function testFilesLimitIsChecked(): void
    {
        $this->overrideSetting('maxfiles', 1);

        $this->upload(UploadedFile::fake()->image('first.jpg', 300, 300))->assertStatus(201);

        $this->upload(UploadedFile::fake()->image('second.jpg', 300, 300))
            ->assertStatus(422)
            ->assertJsonPath('message', __('validator.files_max', ['max' => 1]));
    }

    public function testUnknownTypeIsRejected(): void
    {
        $this->postJson(
            '/api/files',
            ['type' => 'unknown', 'file' => UploadedFile::fake()->image('screen.jpg')],
            $this->headers(),
        )->assertStatus(422)->assertJsonValidationErrors('type');
    }

    public function testMessageFileIsNotUploadedAhead(): void
    {
        // Ожидающий файл сообщения не знает получателя — только в самом запросе
        $this->post(
            '/api/files',
            ['type' => Message::$morphName, 'id' => 0, 'file' => UploadedFile::fake()->createWithContent('note.txt', 'text')],
            $this->headers() + ['Accept' => 'application/json'],
        )->assertStatus(422)->assertJsonValidationErrors('id');

        $this->assertDatabaseMissing('files', ['relate_type' => Message::$morphName]);
    }

    public function testForeignFileIsNotDeleted(): void
    {
        $owner = User::factory()->create();

        $file = File::factory()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => 0,
            'path'        => '/uploads/comments/foreign.jpg',
            'name'        => 'foreign.jpg',
            'size'        => 100,
            'extension'   => 'jpg',
            'mime_type'   => 'image/jpeg',
            'user_id'     => $owner->id,
        ]);

        $this->deleteJson('/api/files/' . $file->id, ['type' => Comment::$morphName], $this->headers())
            ->assertForbidden();

        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }

    private function upload(UploadedFile $file): TestResponse
    {
        return $this->post('/api/files', [
            'type' => Comment::$morphName,
            'id'   => 0,
            'file' => $file,
        ], $this->headers());
    }
}
