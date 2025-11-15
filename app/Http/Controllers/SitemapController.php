<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Down;
use App\Models\News;
use App\Models\Topic;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private array $pages = [
        'news',
        'articles',
        'topics',
        'downs',
    ];

    /**
     * Генерирует главную страницу
     */
    public function index(): Response
    {
        $locs = [];

        foreach ($this->pages as $page) {
            if ($this->$page()) {
                $locs[] = [
                    'loc'     => config('app.url') . '/sitemap/' . $page . '.xml',
                    'lastmod' => $this->$page()[0]['lastmod'],
                ];
            }
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
     * Генерирует новости
     */
    private function news(): array
    {
        return Cache::remember('NewsSitemap', 600, static function () {
            $newses = News::query()
                ->orderByDesc('created_at')
                ->limit(10000)
                ->get();

            $locs = [];
            foreach ($newses as $news) {
                $locs[] = [
                    'loc'     => route('news.view', ['id' => $news->id]),
                    'lastmod' => gmdate('c', $news->created_at),
                ];
            }

            return $locs;
        });
    }

    /**
     * Генерирует статьи
     */
    private function articles(): array
    {
        return Cache::remember('ArticlesSitemap', 600, static function () {
            $articles = Article::query()
                ->active()
                ->orderByDesc('created_at')
                ->limit(10000)
                ->get();

            $locs = [];
            foreach ($articles as $article) {
                $locs[] = [
                    'loc'     => route('articles.view', ['slug' => $article->slug]),
                    'lastmod' => gmdate('c', $article->created_at),
                ];
            }

            return $locs;
        });
    }

    /**
     * Генерирует темы форума
     */
    private function topics(): array
    {
        return Cache::remember('TopicsSitemap', 600, static function () {
            $topics = Topic::query()
                ->orderByDesc('created_at')
                ->limit(10000)
                ->get();

            $locs = [];
            foreach ($topics as $topic) {
                $locs[] = [
                    'loc'     => route('topics.topic', ['id' => $topic->id]),
                    'lastmod' => gmdate('c', $topic->created_at),
                ];
            }

            return $locs;
        });
    }

    /**
     * Генерирует загрузки
     */
    private function downs(): array
    {
        return Cache::remember('DownsSitemap', 600, static function () {
            $downs = Down::query()
                ->active()
                ->orderByDesc('created_at')
                ->limit(10000)
                ->get();

            $locs = [];
            foreach ($downs as $down) {
                $locs[] = [
                    'loc'     => route('downs.view', ['id' => $down->id]),
                    'lastmod' => gmdate('c', $down->created_at),
                ];
            }

            return $locs;
        });
    }
}
