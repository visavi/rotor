<?php

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
        $total = Down::query()->where('active', 1)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downlist'), $total);

        $downs = Down::query()
            ->where('active', 1)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user')
            ->get();

        return view('loads/new_files', compact('downs', 'page'));
    }

    /**
     * Новые комментарии
     *
     * @return string
     */
    public function comments(): string
    {
        $total = Comment::query()->where('relate_type', Down::class)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downcomm'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Down::class)
            ->leftJoin('downs', 'comments.relate_id', '=', 'downs.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('loads/new_comments', compact('comments', 'page'));
    }
}
