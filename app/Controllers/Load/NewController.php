<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;

class NewController extends BaseController
{
    /**
     * Новые файлы
     *
     * @return string
     */
    public function files(): string
    {
        $downs = Down::query()
            ->where('active', 1)
            ->orderByDesc('created_at')
            ->with('category', 'user')
            ->paginate(setting('downlist'));

        return view('loads/new_files', compact('downs'));
    }

    /**
     * Новые комментарии
     *
     * @return string
     */
    public function comments(): string
    {
        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Down::class)
            ->leftJoin('downs', 'comments.relate_id', 'downs.id')
            ->orderByDesc('comments.created_at')
            ->with('user')
            ->paginate(setting('downcomm'));

        return view('loads/new_comments', compact('comments'));
    }
}
