<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public static array $extraPages = [];

    private function getAllPages(): array
    {
        return array_keys(static::$extraPages);
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
        return (static::$extraPages[$page])();
    }
}
