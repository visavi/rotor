<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Down;
use App\Models\News;
use App\Models\Topic;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public array $pages = [
        'news',
        'articles',
        'topics',
        'downs',
    ];

    /**
     * Генерируем главную страницу
     */
    public function index(): Response
    {
        $locs = [];

        foreach ($this->pages as $page) {
            $locs[] = [
                'loc'     => config('app.url') . '/sitemap/' . $page . '.xml',
                'lastmod' => gmdate('Y-m-d', SITETIME),
            ];
        }

        return response()
            ->view('sitemap/index', compact('locs'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Вызывает страницу
     */
    public function page(string $page): Response
    {
        if (! in_array($page, $this->pages, true)) {
            abort(404);
        }

        return response()
            ->view('sitemap/url', ['locs' => $this->$page()])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Генерируем статьи
     */
    private function articles(): array
    {
        $articles = Article::query()
            ->active()
            ->selectRaw('articles.*, max(c.created_at) as last_time')
            ->leftJoin('comments as c', static function (JoinClause $join) {
                $join->on('articles.id', 'c.relate_id')
                    ->where('relate_type', Article::$morphName);
            })
            ->groupBy('articles.id')
            ->orderByDesc('last_time')
            ->get();

        $locs = [];

        foreach ($articles as $article) {
            $changeTime = ($article->last_time > $article->created_at) ? $article->last_time : $article->created_at;

            $locs[] = [
                'loc'     => route('articles.view', ['slug' => $article->slug]),
                'lastmod' => gmdate('c', $changeTime),
            ];
        }

        return $locs;
    }

    /**
     * Генерируем новости
     */
    private function news(): array
    {
        $newses = News::query()
            ->selectRaw('news.*, max(c.created_at) as last_time')
            ->leftJoin('comments as c', static function (JoinClause $join) {
                $join->on('news.id', 'c.relate_id')
                    ->where('relate_type', News::$morphName);
            })
            ->groupBy('news.id')
            ->orderByDesc('last_time')
            ->get();

        $locs = [];

        foreach ($newses as $news) {
            $changeTime = ($news->last_time > $news->created_at) ? $news->last_time : $news->created_at;

            $locs[] = [
                'loc'     => route('news.view', ['id' => $news->id]),
                'lastmod' => gmdate('c', $changeTime),
            ];
        }

        return $locs;
    }

    /**
     * Генерируем темы форума
     */
    private function topics(): array
    {
        $topics = Topic::query()->orderByDesc('updated_at')->limit(15000)->get();

        $locs = [];

        foreach ($topics as $topic) {
            $locs[] = [
                'loc'     => route('topics.topic', ['id' => $topic->id]),
                'lastmod' => gmdate('c', $topic->updated_at),
            ];
        }

        return $locs;
    }

    /**
     * Генерируем загрузки
     */
    private function downs(): array
    {
        $downs = Down::query()
            ->selectRaw('downs.*, max(c.created_at) as last_time')
            ->leftJoin('comments as c', static function (JoinClause $join) {
                $join->on('downs.id', 'c.relate_id')
                    ->where('relate_type', Down::$morphName);
            })
            ->groupBy('downs.id')
            ->orderByDesc('last_time')
            ->get();

        $locs = [];
        foreach ($downs as $down) {
            $changeTime = ($down->last_time > $down->created_at) ? $down->last_time : $down->created_at;

            $locs[] = [
                'loc'     => route('downs.view', ['id' => $down->id]),
                'lastmod' => gmdate('c', $changeTime),
            ];
        }

        return $locs;
    }
}
