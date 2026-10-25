<?php

namespace Tests\Feature;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testSeveralCommentsAreDeleted(): void
    {
        $comments = Comment::factory()->count(2)->create();

        // Пачка: ленивая загрузка файлов в ней запрещена
        Comment::query()->withoutGlobalScopes()->whereKey($comments->modelKeys())->get()->each->delete();

        $this->assertDatabaseMissing('comments', ['id' => $comments->firstOrFail()->id]);
    }

    public function testSeveralCommentsAreSoftDeleted(): void
    {
        $comments = Comment::factory()->count(2)->create();

        Comment::query()->withoutGlobalScopes()->whereKey($comments->modelKeys())->get()->each->softDelete();

        $this->assertNotNull($comments->firstOrFail()->refresh()->deleted_at);
    }
}
