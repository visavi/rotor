<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizer
{
    /**
     * Свойства style, которые ставит редактор: выравнивание, цвет, фон и размер текста
     */
    private const array STYLE_PROPERTIES = ['text-align', 'color', 'background-color', 'font-size'];

    /**
     * Видеохостинги, которые встраивает редактор (resources/js/tiptap.js, toEmbedUrl)
     */
    private const array IFRAME_HOSTS = ['youtube.com', 'youtube-nocookie.com', 'rutube.ru', 'vk.com', 'vkvideo.ru'];

    private static ?SymfonyHtmlSanitizer $instance = null;

    public static function sanitize(?string $text): string
    {
        // Фильтры разбирают вывод Symfony до декодирования сущностей: в нём атрибуты
        // в двойных кавычках, а кавычки в тексте и значениях закодированы
        $html = self::filterIframes(self::filterStyles(self::getInstance()->sanitize((string) $text)));

        // preg_* отдаёт null, упёршись в pcre.backtrack_limit: пустая строка вместо неочищенной.
        // Symfony HtmlSanitizer (через DOMDocument) кодирует безопасные символы как числовые
        // HTML-сущности. Декодируем все кроме &(38), <(60), >(62), "(34) — они нужны в HTML.
        return preg_replace_callback('/&#(\d+);/', static function ($m) {
            $code = (int) $m[1];

            return in_array($code, [34, 38, 60, 62], true) ? $m[0] : mb_chr($code);
        }, $html) ?? '';
    }

    /**
     * Оставляет в style только свойства редактора
     *
     * Symfony чистит атрибуты, но не CSS внутри style: position: fixed и z-index
     * накрывали страницу блоком из комментария, url() подгружал чужой адрес
     */
    private static function filterStyles(string $html): string
    {
        return preg_replace_callback('/\sstyle="([^"]*)"/i', static function (array $match): string {
            $kept = [];

            foreach (explode(';', html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5)) as $declaration) {
                [$property, $value] = array_map(trim(...), explode(':', $declaration, 2)) + [1 => ''];
                $property = strtolower($property);

                if (
                    in_array($property, self::STYLE_PROPERTIES, true)
                    && preg_match('/^[#\w\s(),.%-]+$/u', $value)
                    && ! preg_match('/url|expression|var\s*\(/i', $value)
                ) {
                    $kept[] = $property . ': ' . $value;
                }
            }

            return $kept ? ' style="' . htmlspecialchars(implode('; ', $kept), ENT_QUOTES) . '"' : '';
        }, $html) ?? '';
    }

    /**
     * Убирает iframe не с видеохостингов
     *
     * Symfony проверяет у src только схему, а общий список хостов для медиа отрезал бы
     * и картинки с других сайтов. Без проверки в запись встраивалась любая страница,
     * в том числе поддельная форма входа
     */
    private static function filterIframes(string $html): string
    {
        return preg_replace_callback('#<iframe\b[^>]*>.*?</iframe>#is', static function (array $match): string {
            preg_match('/\ssrc="([^"]*)"/i', $match[0], $src);

            $url = html_entity_decode($src[1] ?? '', ENT_QUOTES | ENT_HTML5);
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));

            if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
                return '';
            }

            foreach (self::IFRAME_HOSTS as $allowed) {
                if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                    return $match[0];
                }
            }

            return '';
        }, $html) ?? '';
    }

    private static function getInstance(): SymfonyHtmlSanitizer
    {
        if (self::$instance === null) {
            $config = (new HtmlSanitizerConfig())
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                // Текст
                ->allowElement('p', ['style'])
                ->allowElement('span', ['style'])
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('br')
                // Списки
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                // Таблицы
                ->allowElement('table', ['class'])
                ->allowElement('tbody')
                ->allowElement('tr')
                ->allowElement('th')
                ->allowElement('td')
                // Цитата
                ->allowElement('blockquote')
                ->allowElement('footer')
                // Код
                ->allowElement('pre', ['class'])
                ->allowElement('code')
                // Ссылки и медиа
                ->allowElement('a', ['href', 'class'])
                ->allowElement('img', ['src', 'alt', 'class'])
                ->allowElement('audio', ['src', 'controls'])
                ->allowElement('video', ['src', 'controls', 'preload', 'class'])
                ->allowElement('iframe', ['src', 'allowfullscreen', 'frameborder', 'loading'])
                // Блоки
                ->allowElement('div', ['class'])
                ->allowElement('details', ['class'])
                ->allowElement('summary')
                ->forceHttpsUrls(false);

            self::$instance = new SymfonyHtmlSanitizer($config);
        }

        return self::$instance;
    }
}
