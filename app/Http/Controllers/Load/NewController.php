<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Down;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewController extends Controller
{
    /**
     * Новые файлы
     */
    public function files(Request $request): View
    {
        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Down::getSorting($sort, $order);

        $downs = Down::query()
            ->active()
            ->with('user', 'category')
            ->orderBy(...$orderBy)
            ->paginate(setting('downlist'))
            ->appends(compact('sort', 'order'));

        return view('loads/new_files', compact('downs', 'sorting'));
    }

    /**
     * Новые комментарии
     */
    public function comments(): View
    {
        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Down::$morphName)
            ->leftJoin('downs', 'comments.relate_id', 'downs.id')
            ->orderByDesc('comments.created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('loads/new_comments', compact('comments'));
    }
}
