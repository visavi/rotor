<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
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

    public function testUnknownTypeIsRejected(): void
    {
        $this->postJson(
            '/api/files',
            ['type' => 'unknown', 'file' => UploadedFile::fake()->image('screen.jpg')],
            $this->headers(),
        )->assertStatus(422)->assertJsonValidationErrors('type');
    }

    public function testForeignFileIsNotDeleted(): void
    {
        $owner = User::factory()->create();

        $file = File::query()->create([
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
            ->assertStatus(422);

        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }
}
