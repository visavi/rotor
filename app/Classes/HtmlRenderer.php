<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\User;
use Illuminate\Support\HtmlString;

class HtmlRenderer
{
    /**
     * Обрабатывает HTML для вывода
     */
    public static function html(?string $text, string $group = 'gallery'): HtmlString
    {
        $html = (string) $text;

        $html = self::hidden($html);
        $html = self::images($html, $group);
        $html = self::mentions($html);
        $html = self::links($html);

        return new HtmlString($html);
    }

    /**
     * Экранирует текст с переносами строк
     */
    public static function text(?string $text): HtmlString
    {
        return new HtmlString(nl2br(e((string) $text)));
    }

    /**
     * Скрывает содержимое от гостей
     */
    protected static function hidden(string $html): string
    {
        if (str_contains($html, 'class="hidden"') && ! auth()->check()) {
            $html = preg_replace(
                '/<div class="hidden">.*?<\/div>/s',
                '<div class="hidden"><em>Содержимое скрыто. Войдите, чтобы увидеть.</em></div>',
                $html
            );
        }

        return $html;
    }

    /**
     * Подключает галерею к изображениям
     */
    protected static function images(string $html, string $group): string
    {
        if (str_contains($html, 'class="image"')) {
            $html = preg_replace(
                '/<img\s([^>]*)class="image"([^>]*)>/i',
                '<img $1class="image" data-fancybox="' . $group . '"$2>',
                $html
            );
        }

        return $html;
    }

    /**
     * Резолвит имена в упоминаниях
     */
    protected static function mentions(string $html): string
    {
        if (str_contains($html, 'class="user"')) {
            $names = User::names();
            $html = preg_replace_callback(
                '#<a class="user" href="/users/([^"/]+)">@[^<]*</a>#',
                static fn ($m) => '<a class="user" href="/users/' . $m[1] . '">@' . e($names[$m[1]] ?? $m[1]) . '</a>',
                $html
            );
        }

        return $html;
    }

    /**
     * Открывает внешние ссылки в новой вкладке
     */
    protected static function links(string $html): string
    {
        if (str_contains($html, '<a ')) {
            $siteHost = parse_url(config('app.url'), PHP_URL_HOST);
            $html = preg_replace_callback(
                '/<a\b([^>]*)>/i',
                static fn ($m) => preg_match('/href="([^"]*)"/i', $m[1], $h)
                && ($host = parse_url($h[1], PHP_URL_HOST))
                && $host !== $siteHost
                    ? '<a' . $m[1] . ' target="_blank" rel="noopener nofollow">'
                    : $m[0],
                $html
            );
        }

        return $html;
    }
}
