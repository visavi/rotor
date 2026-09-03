<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Message;
use App\Models\Spam;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxRatingTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private User $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Relation::morphMap([Comment::$morphName => Comment::class]);

        $this->author = User::factory()->create();
        $this->voter = User::factory()->create();
    }

    public function testVoteReturnsRenderedRatingBlock(): void
    {
        $comment = $this->createComment();

        $response = $this->actingAs($this->voter)->postJson('/ajax/rating', [
            'type' => Comment::$morphName,
            'id'   => $comment->id,
            'vote' => '+',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Стрелки и значение приходят готовой разметкой, клиент их не собирает
        $html = $response->json('html');
        $this->assertStringContainsString('post-rating-up active', $html);
        $this->assertStringContainsString('rating-value', $html);
        $this->assertSame(1, $comment->fresh()->rating);
    }

    public function testRepeatedVoteCancelsAndDropsActiveState(): void
    {
        $comment = $this->createComment();

        $payload = ['type' => Comment::$morphName, 'id' => $comment->id, 'vote' => '+'];

        $this->actingAs($this->voter)->postJson('/ajax/rating', $payload)->assertOk();
        $response = $this->actingAs($this->voter)->postJson('/ajax/rating', $payload);

        // Повторный клик по своей стрелке снимает голос, ответ без пояснения
        $response->assertJsonPath('success', false);
        $this->assertNull($response->json('message'));
    }

    public function testOwnRecordIsNotVotable(): void
    {
        $comment = $this->createComment();

        $this->actingAs($this->author)->postJson('/ajax/rating', [
            'type' => Comment::$morphName,
            'id'   => $comment->id,
            'vote' => '+',
        ])->assertJsonPath('success', false);

        $this->assertSame(0, $comment->fresh()->rating);
    }

    public function testComplaintIsAccepted(): void
    {
        $message = Message::query()->create([
            'user_id'    => $this->voter->id,
            'author_id'  => $this->author->id,
            'text'       => 'Сообщение с жалобой',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->voter)->postJson('/ajax/complaint', [
            'type' => Message::$morphName,
            'id'   => $message->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', __('ajax.complaint_success_sent'));
        $this->assertSame(1, Spam::query()->count());
    }

    private function createComment(): Comment
    {
        return Comment::query()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => 1,
            'text'        => 'Комментарий для голосования',
            'user_id'     => $this->author->id,
            'rating'      => 0,
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
            'created_at'  => now(),
        ]);
    }
}
