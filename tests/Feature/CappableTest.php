<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use App\Traits\CappableTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CappableTest extends TestCase
{
    use RefreshDatabase;

    public function testPagesStayWithinCap(): void
    {
        // Общие ленты листаются по первым N записям: подсчёт и страницы не уходят дальше
        $user = User::factory()->create();

        $ids = collect(range(1, 3))->map(fn (int $i) => Comment::query()->create([
            'relate_type' => Comment::$morphName,
            'relate_id'   => 1,
            'text'        => 'Комментарий ' . $i,
            'user_id'     => $user->id,
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
            'created_at'  => now(),
        ])->id);

        $model = new class extends Comment {
            use CappableTrait;

            protected $table = 'comments';
        };

        $page = $model->newQuery()->orderByDesc('id')->capped(2)->paginate(1, page: 2);

        $this->assertSame(2, $page->total());
        $this->assertSame($ids[1], $page->first()->id);
        $this->assertCount(0, $model->newQuery()->orderByDesc('id')->capped(2)->paginate(1, page: 3));
    }
}
