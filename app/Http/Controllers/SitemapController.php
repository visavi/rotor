<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public static array $extraPages = [];

    private array $pages = [
        'topics',
    ];

    private function getAllPages(): array
    {
        return array_merge($this->pages, array_keys(static::$extraPages));
    }

    /**
     * Генерирует главную страницу
     */
    public function index(): Response
    {
        $locs = [];

        foreach ($this->getAllPages() as $page) {
            $data = $this->getPageData($page);
            if ($data) {
                $locs[] = [
                    'loc'     => config('app.url') . '/sitemap/' . $page . '.xml',
                    'lastmod' => $data[0]['lastmod'],
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
        if (! in_array($page, $this->getAllPages(), true)) {
            abort(404);
        }

        return response()
            ->view('sitemap/url', ['locs' => $this->getPageData($page)])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    private function getPageData(string $page): array
    {
        if (isset(static::$extraPages[$page])) {
            return (static::$extraPages[$page])();
        }

        return $this->$page();
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

}
