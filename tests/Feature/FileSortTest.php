<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileSortTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->overrideSetting('app_installed', 1);

        Relation::morphMap([Comment::$morphName => Comment::class]);

        $this->author = User::factory()->create();
    }

    public function testOrderIsSavedAndUsedByRelation(): void
    {
        $comment = $this->createComment();
        [$first, $second, $third] = $this->createFiles($comment->id, $this->author, 3);

        $response = $this->actingAs($this->author)->postJson('/ajax/file/sort?type=' . Comment::$morphName, [
            'sort' => implode(',', [$third->id, $first->id, $second->id]),
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame([$third->id, $first->id, $second->id], $comment->files()->pluck('id')->all());
    }

    public function testFilesWithoutManualOrderKeepUploadOrder(): void
    {
        $comment = $this->createComment();
        $files = $this->createFiles($comment->id, $this->author, 3, sort: 0);

        $this->assertSame(collect($files)->pluck('id')->all(), $comment->files()->pluck('id')->all());
    }

    public function testForeignFilesAreNotSorted(): void
    {
        $comment = $this->createComment();
        [$first, $second] = $this->createFiles($comment->id, $this->author, 2);

        $response = $this->actingAs(User::factory()->create())->postJson('/ajax/file/sort?type=' . Comment::$morphName, [
            'sort' => $second->id . ',' . $first->id,
        ]);

        $response->assertJsonPath('success', false);
        $this->assertSame([$first->id, $second->id], $comment->files()->pluck('id')->all());
    }

    public function testFilesOfDifferentRecordsAreRejected(): void
    {
        $one = $this->createComment();
        $two = $this->createComment();
        [$first] = $this->createFiles($one->id, $this->author, 1);
        [$second] = $this->createFiles($two->id, $this->author, 1);

        $this->actingAs($this->author)->postJson('/ajax/file/sort?type=' . Comment::$morphName, [
            'sort' => $second->id . ',' . $first->id,
        ])->assertJsonPath('success', false);

        $this->assertSame(1, $first->fresh()->sort);
        $this->assertSame(1, $second->fresh()->sort);
    }

    /**
     * @return array<int, File>
     */
    private function createFiles(int $relateId, User $user, int $count, ?int $sort = null): array
    {
        $files = [];

        for ($i = 1; $i <= $count; $i++) {
            $files[] = File::query()->create([
                'relate_type' => Comment::$morphName,
                'relate_id'   => $relateId,
                'path'        => '/uploads/comments/test' . $i . '.jpg',
                'name'        => 'test' . $i . '.jpg',
                'size'        => 1000,
                'extension'   => 'jpg',
                'mime_type'   => 'image/jpeg',
                'user_id'     => $user->id,
                'sort'        => $sort ?? $i,
                'created_at'  => now(),
            ]);
        }

        return $files;
    }

    private function createComment(): Comment
    {
        return Comment::query()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => 1,
            'text'        => 'Комментарий с вложениями',
            'user_id'     => $this->author->id,
            'rating'      => 0,
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
            'created_at'  => now(),
        ]);
    }
}
