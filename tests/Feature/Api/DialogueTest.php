<?php

namespace Tests\Feature\Api;

use App\Models\Dialogue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function testEmptyDialogueIsNotFound(): void
    {
        $this->deleteJson('/api/talk/' . $this->author->login, [], $this->headers())
            ->assertStatus(404);
    }

    public function testUnknownUserIsNotFound(): void
    {
        $this->deleteJson('/api/talk/nobody', [], $this->headers())->assertStatus(404);
    }

    /**
     * Отмечает переписку прочитанной и обнуляет счётчик у получателя
     */
    private function markAsRead(): void
    {
        Dialogue::query()
            ->where('user_id', $this->user->id)
            ->update(['reading' => 1]);

        $this->user->update(['newprivat' => 0]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->user->apikey];
    }
}
