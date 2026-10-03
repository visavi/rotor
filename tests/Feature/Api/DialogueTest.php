<?php

namespace Tests\Feature\Api;

use App\Models\Dialogue;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class DialogueTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        $this->user = User::factory()->create(['apikey' => Str::random(32)]);
        $this->author = User::factory()->create();
    }

    public function testDeleteRequiresToken(): void
    {
        $this->deleteJson('/api/talk/' . $this->author->login)->assertStatus(400);
    }

    public function testDialogueIsDeleted(): void
    {
        $this->user->sendMessage($this->author, 'Привет');
        $this->markAsRead();

        $this->deleteJson('/api/talk/' . $this->author->login, [], $this->headers())->assertOk();

        $this->assertDatabaseMissing('dialogues', [
            'user_id'   => $this->user->id,
            'author_id' => $this->author->id,
        ]);
    }

    public function testUnreadMessagesBlockDeletion(): void
    {
        $this->user->sendMessage($this->author, 'Привет');

        // Непрочитанное удалять нельзя: на сайте то же условие
        $this->deleteJson('/api/talk/' . $this->author->login, [], $this->headers())
            ->assertStatus(422);

        $this->assertDatabaseHas('dialogues', [
            'user_id'   => $this->user->id,
            'author_id' => $this->author->id,
        ]);
    }

    public function testMessagesSurviveRecipientDeletion(): void
    {
        $message = $this->user->sendMessage($this->author, 'Привет');

        $this->user->delete();

        // У второй стороны переписка и текст сообщения остаются
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
        $this->assertDatabaseHas('dialogues', [
            'message_id' => $message->id,
            'user_id'    => $this->author->id,
        ]);
        $this->assertDatabaseMissing('dialogues', ['user_id' => $this->user->id]);
    }

    public function testMessageIsRemovedWithLastParticipant(): void
    {
        $message = $this->user->sendMessage($this->author, 'Привет');

        $this->user->delete();
        $this->author->delete();

        // Последний участник ушел — текст больше никому не нужен
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
        $this->assertDatabaseMissing('dialogues', ['message_id' => $message->id]);
    }

    public function testEmptyDialogueIsNotFound(): void
    {
        $this->deleteJson('/api/talk/' . $this->author->login, [], $this->headers())
            ->assertStatus(404);
    }

    public function testUnknownUserIsNotFound(): void
    {
        $this->deleteJson('/api/talk/nobody', [], $this->headers())->assertStatus(404);
    }

    public function testSendIgnoresPendingFiles(): void
    {
        // Файл, брошенный в другом диалоге на сайте, не знает получателя:
        // в сообщение уходят только файлы из запроса, и лимит их не учитывает
        $this->overrideSetting('comment_text_min', 1);
        $this->overrideSetting('comment_text_max', 1000);
        $this->overrideSetting('file_extensions', 'txt');
        $this->overrideSetting('filesize', 1024 * 1024);
        $this->overrideSetting('maxfiles', 1);

        $pending = File::query()->create([
            'relate_id'   => 0,
            'relate_type' => Message::$morphName,
            'path'        => '/uploads/messages/other.txt',
            'name'        => 'other.txt',
            'size'        => 1024,
            'extension'   => 'txt',
            'mime_type'   => 'text/plain',
            'user_id'     => $this->user->id,
        ]);

        $id = $this->post('/api/talk/' . $this->author->login, [
            'text'  => 'Привет',
            'files' => [UploadedFile::fake()->createWithContent('note.txt', 'text')],
        ], $this->headers() + ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->json('data.id');

        $files = Message::query()->find($id)->files()->get();

        try {
            $this->assertSame(['note.txt'], $files->pluck('name')->all());
            $this->assertSame(0, $pending->fresh()->relate_id);
        } finally {
            // Файл из запроса реально лёг в public/uploads
            foreach ($files as $file) {
                @unlink(public_path($file->path));
            }
        }
    }

    /**
     * Отмечает переписку прочитанной: счётчик непрочитанных считается по диалогам
     */
    private function markAsRead(): void
    {
        Dialogue::query()
            ->where('user_id', $this->user->id)
            ->update(['reading' => 1]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }
}
