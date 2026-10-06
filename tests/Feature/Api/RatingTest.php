<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private User $voter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Relation::morphMap([Comment::$morphName => Comment::class]);

        $this->author = User::factory()->create(['apikey' => Str::random(32)]);
        $this->voter = User::factory()->create(['apikey' => Str::random(32)]);
    }

    public function testVoteChangesRating(): void
    {
        $comment = $this->createComment();

        $this->vote($this->voter, $comment->id, '+')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cancel', false)
            ->assertJsonPath('rating', 1);
    }

    public function testErrorsAreDistinguishable(): void
    {
        // Нет записи — 404, своя — 403, остальное — 422; у каждого отказа свой текст
        $comment = $this->createComment();

        $this->vote($this->voter, $comment->id + 1000, '+')
            ->assertNotFound()
            ->assertJsonPath('message', __('main.record_not_found'));

        $this->vote($this->author, $comment->id, '+')
            ->assertForbidden()
            ->assertJsonPath('message', __('main.vote_own'));

        $this->vote($this->voter, $comment->id, '+')->assertOk();

        $this->vote($this->voter, $comment->id, '+')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('main.vote_repeat'));

        $this->vote($this->voter, $comment->id, '0')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('main.vote_invalid'));

        $this->assertSame(1, $comment->fresh()->rating);
    }

    public function testOppositeVoteCancelsPrevious(): void
    {
        $comment = $this->createComment();

        $this->vote($this->voter, $comment->id, '+')->assertOk();

        $this->vote($this->voter, $comment->id, '-')
            ->assertOk()
            ->assertJsonPath('cancel', true)
            ->assertJsonPath('rating', 0);
    }

    private function vote(User $user, int $id, string $vote): TestResponse
    {
        return $this->postJson('/api/rating', [
            'type' => Comment::$morphName,
            'id'   => $id,
            'vote' => $vote,
        ], ['Authorization' => 'Bearer ' . $user->apikey]);
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
