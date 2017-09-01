<?php

namespace App\Controllers;

use App\Models\Blog;
use App\Models\Down;
use App\Models\News;
use App\Models\Topic;
use Illuminate\Database\Capsule\Manager as DB;

class SitemapController extends BaseController
{
    /**
     * Генерируем главную страницу
     */
    public function index()
    {
        $pages = [
            'news.xml',
            'blogs.xml',
            'topics.xml',
            'downs.xml',
        ];

        $locs = [];

        foreach ($pages as $page) {
            $locs[] = [
                'loc'     => setting('home') . '/sitemap/' . $page,
                'lastmod' => date('c', SITETIME),
            ];
        }

        return view('sitemap/index', compact('locs'));
    }

    /**
     * Генерируем блоги
     */
    public function blog()
    {
        $blogs = Blog::select('blogs.*', DB::raw('MAX(c.created_at) as last_time'))
            ->leftJoin('comments as c', function($join){
                $join->on('blogs.id', '=', 'c.relate_id')
                    ->where('relate_type', '=', Blog::class);
            })
            ->groupBy('blogs.id')
            ->orderBy('last_time', 'desc')
            ->get();

        $locs = [];
        foreach ($blogs as $blog) {

            $changeTime = ($blog['last_time'] > $blog['created_at']) ? $blog['last_time'] : $blog['created_at'];

            // Обновлено менее 1 месяца
            $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc'        => setting('home') . '/article/' . $blog['id'],
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }
        return view('sitemap/url', compact('locs'));
    }
    /**
     * Генерируем новости
     */
    public function news()
    {
        $newses = News::select('news.*', DB::raw('MAX(c.created_at) as last_time'))
            ->leftJoin('comments as c', function($join){
                $join->on('news.id', '=', 'c.relate_id')
                    ->where('relate_type', '=', News::class);
            })
            ->groupBy('news.id')
            ->orderBy('last_time', 'desc')
            ->get();

        $locs = [];
        foreach ($newses as $news) {

            $changeTime = ($news['last_time'] > $news['created_at']) ? $news['last_time'] : $news['created_at'];

            // Обновлено менее 1 месяца
            $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc'        => setting('home') . '/news/' . $news['id'],
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }
        return view('sitemap/url', compact('locs'));
    }

    /**
     * Генерируем темы форума
     */
    public function topics()
    {
        $topics = Topic::orderBy('updated_at', 'desc')->limit(25000)->get();

        $locs = [];
        foreach ($topics as $topic) {

            // Обновлено менее 1 месяца
            $new = (SITETIME < $topic['updated_at'] + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc'        => setting('home') . '/topic/' . $topic['id'],
                'lastmod'    => date('c', $topic['updated_at']),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }
        return view('sitemap/url', compact('locs'));
    }

    /**
     * Генерируем загрузки
     */
    public function downs()
    {
        $downs = Down::select('downs.*', DB::raw('MAX(c.created_at) as last_time'))
            ->leftJoin('comments as c', function($join){
                $join->on('downs.id', '=', 'c.relate_id')
                    ->where('relate_type', '=', Down::class);
            })
            ->groupBy('downs.id')
            ->orderBy('last_time', 'desc')
            ->get();

        $locs = [];
        foreach ($downs as $down) {

            $changeTime = ($down['last_time'] > $down['time']) ? $down['last_time'] : $down['time'];

            // Обновлено менее 1 месяца
            $new = (SITETIME < $changeTime + 3600 * 24 * 30) ? true : false;

            $locs[] = [
                'loc'        => setting('home') . '/load/down?act=view&id=' . $down['id'],
                'lastmod'    => date('c', $changeTime),
                'changefreq' => $new ? 'weekly' : 'monthly',
                'priority'   => $new ? '1.0' : '0.5',
            ];
        }
        return view('sitemap/url', compact('locs'));
    }

    /**
     * Если вызывается несуществуюший метод
     */
    public function __call($name, $arguments)
    {
        abort(404);
    }
}
