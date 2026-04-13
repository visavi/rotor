<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Sticker;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Конвертирует BB-код в HTML, совместимый с tiptap-редактором.
 *
 * Каждый тег мапится на HTML-структуру, которую tiptap генерирует
 * в renderHTML соответствующей ноды — так после миграции tiptap
 * распарсит контент без потерь.
 */
class BBMigrator
{
    private array $parsers;
    private array $userLogins;
    private array $stickersMap;

    public function __construct()
    {
        $this->userLogins = Cache::remember('migration_user_logins', 3600, static function (): array {
            return User::query()->pluck('login')
                ->mapWithKeys(fn ($login) => [mb_strtolower($login) => true])
                ->all();
        });

        $this->stickersMap = Cache::remember('migration_stickers_map', 3600, static function (): array {
            return Sticker::query()->pluck('name', 'code')->toArray();
        });

        $this->parsers = [
            // [code] парсим первым, чтобы внутри не срабатывали другие bb-теги
            'code' => [
                'pattern'  => '/\[code\](.+?)\[\/code\]/s',
                'callback' => 'codeBlock',
            ],
            'http' => [
                'pattern'  => '%\b(((?<=^|\s)\w+://)[^\s()<>\[\]]+)%s',
                'callback' => 'urlReplace',
            ],
            'link' => [
                'pattern'  => '%\[url\]((\w+://|//|/)[^\s()<>\[\]]+)\[/url\]%s',
                'callback' => 'urlReplace',
            ],
            'namedLink' => [
                'pattern'  => '%\[url\=((\w+://|//|/)[^\s()<>\[\]]+)\](.+?)\[/url\]%s',
                'callback' => 'urlReplace',
            ],
            'image' => [
                'pattern' => '%\[img\]((\w+://|//|/)[^\s()<>\[\]]+\.(jpg|jpeg|png|gif|webp))\[/img\]%',
                'replace' => '<img class="block-image" src="$1">',
            ],
            'bold' => [
                'pattern' => '/\[b\](.+?)\[\/b\]/s',
                'replace' => '<strong>$1</strong>',
            ],
            'italic' => [
                'pattern' => '/\[i\](.+?)\[\/i\]/s',
                'replace' => '<em>$1</em>',
            ],
            'underline' => [
                'pattern' => '/\[u\](.+?)\[\/u\]/s',
                'replace' => '<u>$1</u>',
            ],
            'strike' => [
                'pattern' => '/\[s\](.+?)\[\/s\]/s',
                'replace' => '<s>$1</s>',
            ],
            'fontSize' => [
                'pattern'  => '/\[size\=([1-5])\](.+?)\[\/size\]/s',
                'callback' => 'fontSize',
            ],
            'fontColor' => [
                'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.+?)\[\/color\]/s',
                'replace' => '<span style="color: $1">$2</span>',
                'iterate' => 5,
            ],
            // [center] → <p style="text-align: center"> (санитайзер разрешает style у p, но не у div)
            'center' => [
                'pattern' => '/\[center\](.+?)\[\/center\]/s',
                'replace' => '<p style="text-align: center">$1</p>',
            ],
            'namedQuote' => [
                'pattern'  => '/\[quote\=(.+?)\](.+?)\[\/quote\]/s',
                'callback' => 'namedQuoteReplace',
                'iterate'  => 3,
            ],
            'quote' => [
                'pattern'  => '/\[quote\](.+?)\[\/quote\]/s',
                'callback' => 'quoteReplace',
                'iterate'  => 3,
            ],
            'orderedList' => [
                'pattern'  => '/\[list=1\](.+?)\[\/list\]/s',
                'callback' => 'listReplace',
            ],
            'unorderedList' => [
                'pattern'  => '/\[list\](.+?)\[\/list\]/s',
                'callback' => 'listReplace',
            ],
            'shortSpoiler' => [
                'pattern'  => '/\[spoiler\=(.+?)\](.+?)\[\/spoiler\]/s',
                'callback' => 'spoilerText',
            ],
            'spoiler' => [
                'pattern'  => '/\[spoiler\](.+?)\[\/spoiler\]/s',
                'callback' => 'spoilerText',
            ],
            'hide' => [
                'pattern'  => '/\[hide\](.+?)\[\/hide\]/s',
                'callback' => 'hiddenText',
            ],
            'youtube' => [
                'pattern' => '/\[youtube\](.*youtu(?:\.be\/|be\.com\/.*(?:vi?\/?=?|embed\/)))([\w-]{11}).*\[\/youtube\]/U',
                'replace' => '<div class="block-video"><iframe src="https://www.youtube.com/embed/$2" allowfullscreen="true" frameborder="0" loading="lazy"></iframe></div>',
            ],
            'video' => [
                'pattern'  => '/\[video\](.+?)\[\/video\]/',
                'callback' => 'videoReplace',
            ],
            'audio' => [
                'pattern' => '%\[audio\]((\w+://|//|/)[^\s()<>\[\]]+\.(mp3|ogg|wav|m4a|flac))\[/audio\]%',
                'replace' => '<audio controls="true" src="$1"></audio>',
            ],
            'username' => [
                'pattern'  => '/(?<=^|\s)@([\w\-]{3,20}+)(?=(\s|,|$))/',
                'callback' => 'userReplace',
            ],
        ];
    }

    public static function convert(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $migrator = new self();
        $html = $migrator->parse($text);
        $html = HtmlSanitizer::sanitize($html);

        // Убираем пустые <p></p> в начале и конце после санитайзера
        return preg_replace('/^(<p><\/p>)+|(<p><\/p>)+$/', '', $html);
    }

    /**
     * Конвертирует текст без создания нового экземпляра класса.
     * Используется в миграциях: один экземпляр на весь чанк,
     * кэш стикеров загружается один раз.
     */
    public function convertText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $html = $this->parse($text);
        $html = HtmlSanitizer::sanitize($html);

        $html = str_replace('&#64;', '@', $html);

        // Убираем пустые <p></p> в начале и конце после санитайзера
        return preg_replace('/^(<p><\/p>)+|(<p><\/p>)+$/', '', $html);
    }

    public function parse(string $source): string
    {
        foreach ($this->parsers as $parser) {
            $iterate = $parser['iterate'] ?? 1;
            for ($i = 0; $i < $iterate; $i++) {
                if (isset($parser['callback'])) {
                    $source = preg_replace_callback($parser['pattern'], [$this, $parser['callback']], $source);
                } else {
                    $source = preg_replace($parser['pattern'], $parser['replace'], $source);
                }
            }
        }

        $source = $this->parseStickers($source);

        return $this->wrapParagraphs($source);
    }

    /**
     * Заменяет коды стикеров (:ban) на <img class="sticker"> для tiptap.
     * Коды берутся из БД (без двоеточия), в тексте ищутся с двоеточием.
     */
    private function parseStickers(string $source): string
    {
        $replacements = [];
        foreach ($this->stickersMap as $code => $src) {
            $replacements[':' . $code] = '<img class="sticker" src="' . $src . '" alt="' . $code . '">';
        }

        uksort($replacements, static fn ($a, $b) => strlen($b) - strlen($a));

        return strtr($source, $replacements);
    }

    /**
     * [code] → <pre class="block-code"><code>...</code></pre>
     *
     * Содержимое экранируется, плюс [ и @ нейтрализуются, чтобы
     * последующие парсеры не сработали внутри блока кода.
     */
    private function codeBlock(array $match): string
    {
        $content = htmlspecialchars($match[1], ENT_QUOTES, 'UTF-8');
        $content = strtr($content, ['[' => '&#91;', '@' => '&#64;']);

        return '<pre class="block-code"><code>' . $content . '</code></pre>';
    }

    /**
     * [url], [url=...], автоссылка → <a href target="_blank">
     */
    private function urlReplace(array $match): string
    {
        $url = $match[1];
        $name = $match[3] ?? $url;
        $attrs = str_starts_with($url, '/') ? '' : ' target="_blank"';

        return '<a href="' . $url . '"' . $attrs . '>' . $name . '</a>';
    }

    /**
     * [list] / [list=1] → <ul>/<ol>. Элементы разделены переводами строк.
     * Внутри li ставим <p>, т.к. tiptap рендерит li как block+ с параграфом.
     */
    private function listReplace(array $match): string
    {
        $items = preg_split('/\R/', $match[1], -1, PREG_SPLIT_NO_EMPTY);

        if (empty($items)) {
            return $match[0];
        }

        $tag = str_contains($match[0], '[list]') ? 'ul' : 'ol';
        $li = '';
        foreach ($items as $item) {
            $li .= '<li><p>' . trim($item) . '</p></li>';
        }

        return '<' . $tag . '>' . $li . '</' . $tag . '>';
    }

    /**
     * [size=1..5] → <span style="font-size: Xem"> (значения совпадают с SIZES в tiptap.js).
     * Средний размер не оборачивается — это дефолт tiptap (null).
     */
    private function fontSize(array $match): string
    {
        $sizes = [1 => '0.7em', 2 => '0.85em', 3 => null, 4 => '1.3em', 5 => '1.6em'];
        $size = $sizes[(int) $match[1]] ?? null;

        if ($size === null) {
            return $match[2];
        }

        return '<span style="font-size: ' . $size . '">' . $match[2] . '</span>';
    }

    /**
     * [video] → <div class="block-video"><iframe ...></iframe></div>
     * Набор хостингов совпадает с getEmbedUrl() в tiptap.js.
     */
    private function videoReplace(array $match): string
    {
        $url = $match[1];
        $embed = null;

        if (preg_match('/youtu(?:\.be\/|be\.com\/.*(?:vi?\/?=|embed\/|watch\?v=))([\w-]{11})/i', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/' . $m[1];
        } elseif (preg_match('/vimeo\.com\/(?:embed\/)?(\d+)/i', $url, $m)) {
            $embed = 'https://player.vimeo.com/video/' . $m[1];
        } elseif (preg_match('/rutube\.ru\/(?:video\/|play\/embed\/)([a-f0-9]+)/i', $url, $m)) {
            $embed = 'https://rutube.ru/play/embed/' . $m[1] . '/';
        } elseif (preg_match('/coub\.com\/(?:view|embed)\/([a-zA-Z0-9]+)/i', $url, $m)) {
            $embed = 'https://coub.com/embed/' . $m[1];
        } elseif (preg_match('/(?:vk\.com\/video|vkvideo\.ru\/video)(-?\d+)_(\d+)/i', $url, $m)) {
            $embed = 'https://vk.com/video_ext.php?oid=' . $m[1] . '&id=' . $m[2] . '&hd=2';
        } elseif (preg_match('/ok\.ru\/(?:video|video\/embed)\/(\d+)/i', $url, $m)) {
            $embed = 'https://ok.ru/videoembed/' . $m[1];
        }

        if ($embed === null) {
            return $match[0];
        }

        return '<div class="block-video"><iframe src="' . $embed . '" allowfullscreen="true" frameborder="0" loading="lazy"></iframe></div>';
    }

    /**
     * @username → <a class="mention" href="/users/ID">@ID</a>
     * (структура Mention-ноды tiptap).
     */
    private function userReplace(array $match): string
    {
        if (! isset($this->userLogins[mb_strtolower($match[1])])) {
            return $match[0];
        }

        return '<a class="mention" href="/users/' . $match[1] . '">@' . $match[1] . '</a>';
    }

    /**
     * [hide] → <div class="block-hidden"> (класс Hide-ноды tiptap).
     */
    private function hiddenText(array $match): string
    {
        return '<div class="block-hidden">' . $match[1] . '</div>';
    }

    /**
     * [quote=author] → <blockquote><div><p>...</p></div><footer>author</footer></blockquote>
     */
    private function namedQuoteReplace(array $match): string
    {
        $inner = $this->wrapParagraphs($match[2]);

        return '<blockquote><div>' . $inner . '</div><footer>' . $match[1] . '</footer></blockquote>';
    }

    /**
     * [quote] → <blockquote><p>...</p></blockquote>
     */
    private function quoteReplace(array $match): string
    {
        $inner = $this->wrapParagraphs($match[1]);

        return '<blockquote>' . $inner . '</blockquote>';
    }

    /**
     * [spoiler] / [spoiler=title] → <details class="block-spoiler">
     * (структура Spoiler-ноды tiptap).
     */
    private function spoilerText(array $match): string
    {
        $title = empty($match[2]) ? 'Spoiler' : $match[1];
        $text = empty($match[2]) ? $match[1] : $match[2];

        return '<details class="block-spoiler" open="true"><summary>' . $title . '</summary><div>' . $text . '</div></details>';
    }

    /**
     * Конвертирует текстовые строки в параграфы tiptap:
     *   - одиночный перенос строки  → <br>  (строки одного абзаца)
     *   - пустая строка             → граница <p>
     *   - блочные элементы          → выводятся как есть (без лишней обёртки)
     */
    private function wrapParagraphs(string $html): string
    {
        $html = ltrim($html);

        $containerTags = ['p', 'details', 'div', 'blockquote', 'pre', 'ul', 'ol'];

        $anyOpen = '/<(' . implode('|', $containerTags) . ')[\s>]/i';
        $anyClose = '/<\/(' . implode('|', $containerTags) . ')>/i';
        // <audio> и <img class="block-image"> — блочные; <img class="sticker"> — инлайн, не матчим
        $startsWithBlock = '/^(?:<(?:' . implode('|', $containerTags) . ')[\s>\/]|<audio[\s>\/]|<img\s[^>]*class="block-image")/i';

        $lines = explode("\n", $html);
        $result = '';
        $blockBuffer = [];
        $paraLines = [];
        $depth = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (! empty($blockBuffer) || $depth > 0) {
                // Внутри блочного элемента — буферизуем строку целиком
                $blockBuffer[] = $line;
                $depth += preg_match_all($anyOpen, $trimmed, $m);
                $depth -= preg_match_all($anyClose, $trimmed, $m);

                if ($depth <= 0) {
                    if (! empty($paraLines)) {
                        $result .= '<p>' . implode('<br>', $paraLines) . '</p>';
                        $paraLines = [];
                    }
                    $result .= implode("\n", $blockBuffer);
                    $blockBuffer = [];
                    $depth = 0;
                }
            } elseif (preg_match($startsWithBlock, $trimmed)) {
                // Строка начинается с блочного тега — сбрасываем текущий абзац
                if (! empty($paraLines)) {
                    $result .= '<p>' . implode('<br>', $paraLines) . '</p>';
                    $paraLines = [];
                }
                $blockBuffer[] = $line;
                $depth += preg_match_all($anyOpen, $trimmed, $m);
                $depth -= preg_match_all($anyClose, $trimmed, $m);

                if ($depth <= 0) {
                    $result .= implode("\n", $blockBuffer);
                    $blockBuffer = [];
                    $depth = 0;
                }
            } elseif ($trimmed === '') {
                // Пустая строка — сбрасываем абзац и добавляем пустой <p>
                if (! empty($paraLines)) {
                    $result .= '<p>' . implode('<br>', $paraLines) . '</p>';
                    $paraLines = [];
                }
                $result .= '<p></p>';
            } else {
                // Обычная текстовая строка — накапливаем в абзац
                $paraLines[] = $trimmed;
            }
        }

        if (! empty($paraLines)) {
            $result .= '<p>' . implode('<br>', $paraLines) . '</p>';
        }

        if (! empty($blockBuffer)) {
            $result .= implode("\n", $blockBuffer);
        }

        return $result;
    }
}
