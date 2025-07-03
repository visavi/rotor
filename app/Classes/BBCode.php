<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Sticker;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Класс обработки BB-кодов
 *
 * @license Code and contributions have MIT License
 *
 * @link    https://visavi.net
 *
 * @author  Alexander Grigorev <admin@visavi.net>
 */
class BBCode
{
    protected array $parsers = [
        'code' => [
            'pattern'  => '/\[code\](.+?)\[\/code\]/s',
            'callback' => 'highlightCode',
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
            'replace' => '<div class="media-file"><a href="$1" data-fancybox="gallery"><img src="$1" class="img-fluid" alt="image"></a></div>',
        ],
        'bold' => [
            'pattern' => '/\[b\](.+?)\[\/b\]/s',
            'replace' => '<strong>$1</strong>',
        ],
        'italic' => [
            'pattern' => '/\[i\](.+?)\[\/i\]/s',
            'replace' => '<em>$1</em>',
        ],
        'underLine' => [
            'pattern' => '/\[u\](.+?)\[\/u\]/s',
            'replace' => '<u>$1</u>',
        ],
        'lineThrough' => [
            'pattern' => '/\[s\](.+?)\[\/s\]/s',
            'replace' => '<del>$1</del>',
        ],
        'fontSize' => [
            'pattern'  => '/\[size\=([1-5])\](.+?)\[\/size\]/s',
            'callback' => 'fontSize',
        ],
        'fontColor' => [
            'pattern' => '/\[color\=(#[A-f0-9]{6}|#[A-f0-9]{3})\](.+?)\[\/color\]/s',
            'replace' => '<span style="color:$1">$2</span>',
            'iterate' => 5,
        ],
        'center' => [
            'pattern' => '/\[center\](.+?)\[\/center\]/s',
            'replace' => '<div style="text-align:center;">$1</div>',
        ],
        'quote' => [
            'pattern' => '/\[quote\](.+?)\[\/quote\]/s',
            'replace' => '<blockquote class="blockquote">$1</blockquote>',
            'iterate' => 3,
        ],
        'namedQuote' => [
            'pattern' => '/\[quote\=(.+?)\](.+?)\[\/quote\]/s',
            'replace' => '<blockquote class="blockquote">$2<footer class="blockquote-footer">$1</footer></blockquote>',
            'iterate' => 3,
        ],
        'orderedList' => [
            'pattern'  => '/\[list=1\](.+?)\[\/list\]/s',
            'callback' => 'listReplace',
        ],
        'unorderedList' => [
            'pattern'  => '/\[list\](.+?)\[\/list\]/s',
            'callback' => 'listReplace',
        ],
        'spoiler' => [
            'pattern'  => '/\[spoiler\](.+?)\[\/spoiler\]/s',
            'callback' => 'spoilerText',
            'iterate'  => 1,
        ],
        'shortSpoiler' => [
            'pattern'  => '/\[spoiler\=(.+?)\](.+?)\[\/spoiler\]/s',
            'callback' => 'spoilerText',
            'iterate'  => 1,
        ],
        'hide' => [
            'pattern'  => '/\[hide\](.+?)\[\/hide\]/s',
            'callback' => 'hiddenText',
        ],
        'youtube' => [
            'pattern' => '/\[youtube\](.*youtu(?:\.be\/|be\.com\/.*(?:vi?\/?=?|embed\/)))([\w-]{11}).*\[\/youtube\]/U',
            'replace' => '<div class="media-file ratio ratio-16x9"><iframe src="//www.youtube.com/embed/$2" allowfullscreen></iframe></div>',
        ],
        'video' => [
            'pattern'  => '/\[video\](.+?)\[\/video\]/',
            'callback' => 'videoReplace',
        ],
        'audio' => [
            'pattern' => '%\[audio\]((\w+://|//|/)[^\s()<>\[\]]+\.(mp3|ogg|wav|m4a|flac))\[/audio\]%',
            'replace' => '<div><audio src="$1" style="max-width:100%;" preload="metadata" controls></audio></div>',
        ],
        'username' => [
            'pattern'  => '/(?<=^|\s)@([\w\-]{3,20}+)(?=(\s|,|$))/',
            'callback' => 'userReplace',
        ],
        'hashtag' => [
            'pattern'  => '/(?<!\w)#([\p{L}][\p{L}\w\-\+]{2,29})(?!\w)/u',
            'callback' => 'hashtagReplace',
        ],
    ];

    /*
     * Video
     */
    public function videoReplace(array $match): string
    {
        // Проверяем, является ли ссылка YouTube
        if (preg_match('/youtu(?:\.be\/|be\.com\/.*(?:vi?\/?=|embed\/|watch\?v=))([\w-]{11})/i', $match[1], $youtubeMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//www.youtube.com/embed/' . $youtubeMatches[1] . '" allowfullscreen></iframe></div>';
        }

        // Проверяем, является ли ссылка VK
        if (preg_match('/(?:vk\.com\/video|vkvideo\.ru\/video)(-?\d+)_(\d+)/i', $match[1], $vkMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//vk.com/video_ext.php?oid=' . $vkMatches[1] . '&id=' . $vkMatches[2] . '" allowfullscreen></iframe></div>';
        }

        // Проверяем, является ли ссылка Rutube
        if (preg_match('/rutube\.ru\/(?:video\/|play\/embed\/)([a-zA-Z0-9]+)/i', $match[1], $rutubeMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//rutube.ru/play/embed/' . $rutubeMatches[1] . '" allowfullscreen></iframe></div>';
        }

        // Проверяем, является ли ссылка Vimeo
        if (preg_match('/vimeo\.com\/(?:embed\/)?(\d+)/i', $match[1], $vimeoMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//player.vimeo.com/video/' . $vimeoMatches[1] . '" allowfullscreen></iframe></div>';
        }

        // Проверяем, является ли ссылка Coub
        if (preg_match('/coub\.com\/(?:view|embed)\/([a-zA-Z0-9]+)/i', $match[1], $coubMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//coub.com/embed/' . $coubMatches[1] . '" allowfullscreen></iframe></div>';
        }

        // Проверяем, является ли ссылка Ok.ru
        if (preg_match('/ok\.ru\/(?:video|video\/embed)\/(\d+)/i', $match[1], $okMatches)) {
            return '<div class="media-file ratio ratio-16x9"><iframe src="//ok.ru/videoembed/' . $okMatches[1] . '" allowfullscreen></iframe></div>';
        }

        return $match[0];
    }

    /**
     * Обрабатывает текст
     */
    public function parse(string $source): string
    {
        $source = nl2br($source, false);
        $source = str_replace('[cut]', '', $source);

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

        return $this->clearBreakLines($source);
    }

    /**
     * Clear break lines
     */
    private function clearBreakLines(string $source): string
    {
        $tags = [
            '</div><br>'        => '</div>',
            '</pre><br>'        => '</pre>',
            '</blockquote><br>' => '</blockquote>',
        ];

        return strtr($source, $tags);
    }

    /**
     * Очищает текст от BB-кодов
     */
    public function clear(string $source): string
    {
        return preg_replace('/\[(.*?)]/', '', $source);
    }

    /**
     * Обрабатывает ссылки
     */
    public function urlReplace(array $match): string
    {
        $name = $match[3] ?? $match[1];

        $target = '';
        if ($match[2] !== '/') {
            if (str_contains($match[1], request()->getHost())) {
                $match[1] = '//' . str_replace($match[2], '', $match[1]);
            } else {
                $target = ' target="_blank" rel="nofollow"';
            }
        }

        return '<a href="' . $match[1] . '"' . $target . '>' . rawurldecode($name) . '</a>';
    }

    /**
     * Обрабатывает списки
     */
    public function listReplace(array $match): string
    {
        $li = preg_split('/<br>\R/', $match[1], -1, PREG_SPLIT_NO_EMPTY);

        if (empty($li)) {
            return $match[0];
        }

        $list = [];
        foreach ($li as $l) {
            $list[] = '<li>' . $l . '</li>';
        }

        $tag = str_contains($match[0], '[list]') ? 'ul' : 'ol';

        return '<' . $tag . '>' . implode($list) . '</' . $tag . '>';
    }

    /**
     * Обрабатывает размер текста
     */
    public function fontSize(array $match): string
    {
        $sizes = [1 => 'x-small', 2 => 'small', 3 => 'medium', 4 => 'large', 5 => 'x-large'];

        return '<span style="font-size:' . $sizes[$match[1]] . '">' . $match[2] . '</span>';
    }

    /**
     * Подсвечивает код
     */
    public function highlightCode(array $match): string
    {
        // Чтобы bb-код, стикеры и логины не работали внутри тега [code]
        $match[1] = strtr($match[1], [':' => '&#58;', '[' => '&#91;', '@' => '&#64;', '<br>' => '']);

        return '<pre class="prettyprint">' . $match[1] . '</pre>';
    }

    /**
     * Скрывает текст под спойлер
     */
    public function spoilerText(array $match): string
    {
        $title = empty($match[2]) ? __('main.expand_view') : $match[1];
        $text = empty($match[2]) ? $match[1] : $match[2];

        return '<div class="spoiler">
                <b class="spoiler-title">' . $title . '</b>
                <div class="spoiler-text" style="display: none;">' . $text . '</div>
            </div>';
    }

    /**
     * Скрывает текст от неавторизованных пользователей
     */
    public function hiddenText(array $match): string
    {
        return '<div class="hidden-text">
                <span class="fw-bold">' . __('main.hidden_content') . ':</span> ' .
                (getUser() ? $match[1] : __('main.not_authorized')) .
            '</div>';
    }

    /**
     * Обрабатывает логины пользователей
     */
    public function userReplace(array $match): string
    {
        static $listUsers;

        if (empty($listUsers)) {
            $listUsers = Cache::remember('users', 3600, static function () {
                return User::query()
                    ->where('point', '>', 0)
                    ->pluck('name', 'login')
                    ->mapWithKeys(fn ($name, $login) => [strtolower($login) => $name])
                    ->toArray();
            });
        }

        $login = strtolower($match[1]);
        if (! array_key_exists($login, $listUsers)) {
            return $match[0];
        }

        $name = $listUsers[$login] ?: $match[1];

        return '<a href="/users/' . $match[1] . '">' . check($name) . '</a>';
    }

    /**
     * Обрабатывает хештеги
     */
    public function hashtagReplace(array $match): string
    {
        return '<a href="' . route('search', ['query' => check($match[1])]) . '">' . check($match[0]) . '</a>';
    }

    /**
     * Обрабатывает стикеры
     */
    public function parseStickers(string $source): string
    {
        static $listStickers;

        if (empty($listStickers)) {
            $listStickers = Cache::rememberForever('stickers', static function () {
                return Sticker::query()
                    ->select('code', 'name')
                    ->orderByDesc(DB::raw('CHAR_LENGTH(code)'))
                    ->get()
                    ->pluck('name', 'code')
                    ->map(function ($item) {
                        return '<img src="' . $item . '" alt="' . getBodyName($item) . '">';
                    })
                    ->toArray();
            });
        }

        return strtr($source, $listStickers);
    }

    /**
     * Закрывает bb-теги
     */
    public function closeTags(string $text): string
    {
        preg_match_all('#\[([a-z]+)(?:=.*)?(?<!/)]#iU', $text, $result);
        $openTags = $result[1];

        preg_match_all('#\[/([a-z]+)]#iU', $text, $result);
        $closedTags = $result[1];

        if ($openTags === $closedTags) {
            return $text;
        }

        $diff = array_diff_assoc($openTags, $closedTags);
        $tags = array_reverse($diff);

        foreach ($tags as $value) {
            $text .= '[/' . $value . ']';
        }

        return $text;
    }

    /**
     * Добавляет или переопределяет парсер
     */
    public function setParser(string $name, string $pattern, string $replace): void
    {
        $this->parsers[$name] = [
            'pattern' => $pattern,
            'replace' => $replace,
        ];
    }

    /**
     * Устанавливает список доступных парсеров
     */
    public function only(mixed $only = null): self
    {
        $only = is_array($only) ? $only : func_get_args();
        $this->parsers = $this->arrayOnly($only);

        return $this;
    }

    /**
     * Исключает парсеры из набора
     */
    public function except(mixed $except = null): self
    {
        $except = is_array($except) ? $except : func_get_args();
        $this->parsers = $this->arrayExcept($except);

        return $this;
    }

    /**
     * Возвращает список всех парсеров
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Filters all parsers that you don´t want
     */
    private function arrayOnly(array $only): array
    {
        return array_intersect_key($this->parsers, array_flip($only));
    }

    /**
     * Removes the parsers that you don´t want
     */
    private function arrayExcept(array $excepts): array
    {
        return array_diff_key($this->parsers, array_flip($excepts));
    }
}
