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
        return self::getInstance()->sanitize((string) $text);
    }

    private static function getInstance(): SymfonyHtmlSanitizer
    {
        if (self::$instance === null) {
            self::$instance = new SymfonyHtmlSanitizer(
                (new HtmlSanitizerConfig())
                    // Текст
                    ->allowElement('p', ['style'])
                    ->allowElement('span', ['style'])
                    ->allowElement('strong')
                    ->allowElement('em')
                    ->allowElement('u')
                    ->allowElement('s')
                    ->allowElement('br')
                    ->allowElement('mark', ['style'])
                    // Заголовки
                    ->allowElement('h1')
                    ->allowElement('h2')
                    ->allowElement('h3')
                    ->allowElement('h4')
                    ->allowElement('h5')
                    ->allowElement('h6')
                    // Списки
                    ->allowElement('ul')
                    ->allowElement('ol')
                    ->allowElement('li')
                    // Цитата
                    ->allowElement('blockquote')
                    ->allowElement('footer')
                    // Код
                    ->allowElement('pre', ['class'])
                    ->allowElement('code')
                    // Ссылки и медиа
                    ->allowElement('a', ['href', 'target', 'rel'])
                    ->allowElement('img', ['src', 'alt', 'title', 'class'])
                    ->allowElement('audio', ['src', 'controls'])
                    ->allowElement('iframe', ['src', 'allowfullscreen', 'frameborder', 'loading'])
                    // Блоки
                    ->allowElement('div', ['class'])
                    ->allowElement('details', ['class'])
                    ->allowElement('summary')
                    ->allowAttribute('style', ['span', 'p', 'mark'])
                    ->allowAttribute('target', ['a'])
                    ->forceHttpsUrls(false)
            );
        }

        return self::$instance;
    }
}
