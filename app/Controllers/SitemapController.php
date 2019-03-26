<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Blog;
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
        'blogs',
        'topics',
        'downs',
    ];

    /**
     * Генерируем главную страницу
     */
    public function index()
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
     * @param $page
     * @return string
     */
    public function page(string $page): string
    {
        if (! \in_array($page, $this->pages, true)) {
            abort(404);
        }

        return $this->$page();
    }

    /**
     * Генерируем блоги
     *
     * @return string
     */
    private function blogs(): string
    {
        $blogs = Blog::query()
            ->selectRaw('blogs.*, max(c.created_at) as last_time')
            ->leftJoin('comments as c', static function (JoinClause $join) {
                $join->on('blogs.id', 'c.relate_id')
                    ->where('relate_type', Blog::class);
            })
            ->groupBy('blogs.id')
            ->orderBy('last_time', 'desc')
            ->get();

        $locs = [];

        foreach ($blogs as $blog) {
            $changeTime = ($blog->last_time > $blog->created_at) ? $blog->last_time : $blog->created_at;

            // Обновлено менее 1 месяца
            $new = SITETIME < strtotime('+1 month', $changeTime);

            $locs[] = [
                'loc'        => siteUrl(true) . '/articles/' . $blog->id,
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
                    ->where('relate_type', News::class);
            })
            ->groupBy('news.id')
            ->orderBy('last_time', 'desc')
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
        $topics = Topic::query()->orderBy('updated_at', 'desc')->limit(15000)->get();

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
                    ->where('relate_type', Down::class);
            })
            ->groupBy('downs.id')
            ->orderBy('last_time', 'desc')
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
