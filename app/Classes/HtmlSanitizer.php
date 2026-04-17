<?php

declare(strict_types=1);

namespace App\Classes;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizer
{
    private static ?SymfonyHtmlSanitizer $instance = null;

    public static function sanitize(?string $text): string
    {
        $html = self::getInstance()->sanitize((string) $text);

        // Symfony HtmlSanitizer (через DOMDocument) кодирует безопасные символы как числовые
        // HTML-сущности. Декодируем все кроме &(38), <(60), >(62), "(34) — они нужны в HTML.
        return preg_replace_callback('/&#(\d+);/', static function ($m) {
            $code = (int) $m[1];

            return in_array($code, [34, 38, 60, 62], true) ? $m[0] : mb_chr($code);
        }, $html);
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
