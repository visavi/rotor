<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Article;
use App\Models\Down;
use App\Models\News;
use App\Models\Topic;
use Illuminate\Database\Query\JoinClause;

class SitemapController extends BaseController
{
    /**
     * @var array
     */
    public $pages = [
        'news',
        'articles',
        'topics',
        'downs',
    ];

    /**
     * Генерируем главную страницу
     *
     *  @return string
     */
    public function index(): string
    {
        $locs = [];

        foreach ($this->pages as $page) {
            $locs[] = [
                'loc'     => siteUrl(true) . '/sitemap/' . $page . '.xml',
                'lastmod' => date('c', SITETIME),
            ];
        }

        return view('sitemap/index', compact('locs'));
    }

    /**
     * Вызывает страницу
     *
     * @param string $page
     *
     * @return string
     */
    public function page(string $page): string
    {
        if (! in_array($page, $this->pages, true)) {
            abort(404);
        }

        return $this->$page();
    }

    /**
     * Генерируем статьи
     *
     * @return string
     */
    private function articles(): string
    {
        $articles = Article::query()
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

            // Обновлено менее 1 месяца
            $new = SITETIME < strtotime('+1 month', $changeTime);

            $locs[] = [
                'loc'        => siteUrl(true) . '/articles/' . $article->id,
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }

        return view('sitemap/url', compact('locs'));
    }

    /**
     * Генерируем новости
     *
     * @return string
     */
    private function news(): string
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

            // Обновлено менее 1 месяца
            $new = SITETIME < strtotime('+1 month', $changeTime);

            $locs[] = [
                'loc'        => siteUrl(true) . '/news/' . $news->id,
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }

        return view('sitemap/url', compact('locs'));
    }

    /**
     * Генерируем темы форума
     *
     * @return string
     */
    private function topics(): string
    {
        $topics = Topic::query()->orderByDesc('updated_at')->limit(15000)->get();

        $locs = [];

        foreach ($topics as $topic) {
            // Обновлено менее 1 месяца
            $new = SITETIME < strtotime('+1 month', $topic->updated_at);

            $locs[] = [
                'loc'        => siteUrl(true) . '/topics/' . $topic->id,
                'lastmod'    => date('c', $topic->updated_at),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }
        return view('sitemap/url', compact('locs'));
    }

    /**
     * Генерируем загрузки
     *
     * @return string
     */
    private function downs(): string
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

            // Обновлено менее 1 месяца
            $new = SITETIME < strtotime('+1 month', $changeTime);

            $locs[] = [
                'loc'        => siteUrl(true) . '/downs/' . $down->id,
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }

        return view('sitemap/url', compact('locs'));
    }
}
