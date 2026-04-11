<?php

declare(strict_types=1);

namespace App\Classes;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizer
{
    /** @var array<string, SymfonyHtmlSanitizer> */
    private static array $instances = [];

    public static function sanitize(?string $text, bool $extended = false): string
    {
        return self::getInstance($extended)->sanitize((string) $text);
    }

    private static function getInstance(bool $extended = false): SymfonyHtmlSanitizer
    {
        $key = $extended ? 'extended' : 'default';

        if (! isset(self::$instances[$key])) {
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
                ->allowElement('mark', ['style'])
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
                ->allowElement('a', ['href', 'target', 'class'])
                ->allowElement('img', ['src', 'alt', 'class'])
                ->allowElement('audio', ['src', 'controls'])
                ->allowElement('iframe', ['src', 'allowfullscreen', 'frameborder', 'loading'])
                // Блоки
                ->allowElement('div', ['class'])
                ->allowElement('details', ['class'])
                ->allowElement('summary')
                ->forceHttpsUrls(false);

            if ($extended) {
                $config = $config
                    // Заголовки (только для расширенного режима)
                    ->allowElement('h1')
                    ->allowElement('h2')
                    ->allowElement('h3')
                    ->allowElement('h4')
                    ->allowElement('h5')
                    ->allowElement('h6');
            }

            self::$instances[$key] = new SymfonyHtmlSanitizer($config);
        }

        return self::$instances[$key];
    }
}
