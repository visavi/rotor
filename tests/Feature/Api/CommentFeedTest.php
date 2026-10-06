<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommentFeedTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Relation::morphMap([Comment::$morphName => Comment::class]);

        $this->user = User::factory()->create(['apikey' => Str::random(32)]);
    }

    public function testFeedIsNewestFirstWithUserVote(): void
    {
        $old = $this->createComment(User::factory()->create(), now()->subHour());
        $fresh = $this->createComment(User::factory()->create(), now());

        Poll::query()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => $fresh->id,
            'user_id'     => $this->user->id,
            'vote'        => '+',
            'created_at'  => now(),
        ]);

        $this->getJson('/api/comments', ['Authorization' => 'Bearer ' . $this->user->apikey])
            ->assertOk()
            ->assertJsonPath('data.0.id', $fresh->id)
            ->assertJsonPath('data.0.vote.value', '+')
            ->assertJsonPath('data.1.id', $old->id);
    }

    public function testFeedFiltersByUser(): void
    {
        $own = $this->createComment($this->user, now());
        $this->createComment(User::factory()->create(), now());

        $this->getJson('/api/comments?user=' . $this->user->login)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);

        $this->getJson('/api/comments?user=nobody_' . Str::random(8))->assertNotFound();
    }

    public function testFeedRejectsUnknownType(): void
    {
        $this->getJson('/api/comments?type=unknown')->assertUnprocessable();
    }

    public function testDeletedCommentsAreHidden(): void
    {
        $this->createComment($this->user, now(), deleted: true);

        $this->getJson('/api/comments')->assertOk()->assertJsonCount(0, 'data');
    }

    private function createComment(User $user, \DateTimeInterface $createdAt, bool $deleted = false): Comment
    {
        return Comment::query()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => 1,
            'text'        => 'Комментарий',
            'user_id'     => $user->id,
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
            'created_at'  => $createdAt,
            'deleted_at'  => $deleted ? now() : null,
        ]);
    }
}
