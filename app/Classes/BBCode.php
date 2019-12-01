<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Sticker;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Cache;

/**
 * Класс обработки BB-кодов
 *
 * @license Code and contributions have MIT License
 * @link    https://visavi.net
 * @author  Alexander Grigorev <admin@visavi.net>
 */
class BBCode
{
    /**
     * @var array
     */
    protected static $parsers = [
        'code' => [
            'pattern'  => '/\[code\](.+?)\[\/code\]/s',
            'callback' => 'highlightCode'
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
            'pattern' => '%\[img\]((\w+://|//|/)[^\s()<>\[\]]+\.(jpg|png|gif|jpeg))\[/img\]%s',
            'replace' => '<img src="$1" class="img-fluid" alt="image">',
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
            'replace' => '<blockquote>$1</blockquote>',
            'iterate' => 3,
        ],
        'namedQuote' => [
            'pattern' => '/\[quote\=(.+?)\](.+?)\[\/quote\]/s',
            'replace' => '<blockquote>$2<small>$1</small></blockquote>',
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
            'replace' => '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//www.youtube.com/embed/$2" allowfullscreen></iframe></div>',
        ],
        'username' => [
            'pattern' => '/(?<=^|\s)@([\w\-]{3,20}+)(?=(\s|,))/',
            'replace' => '<a href="/users/$1">$0</a>',
        ],
    ];

    /**
     * Обрабатывает текст
     *
     * @param  string $source текст содержаший BBCode
     * @return string         распарсенный текст
     */
    public function parse($source): string
    {
        $source = nl2br($source, false);
        $source = str_replace('[cut]', '', $source);

        foreach (self::$parsers as $parser) {
            $iterate = $parser['iterate'] ?? 1;

            for ($i = 0; $i < $iterate; $i++) {
                if (isset($parser['callback'])) {
                    $source = preg_replace_callback($parser['pattern'], [$this, $parser['callback']], $source);
                } else {
                    $source = preg_replace($parser['pattern'], $parser['replace'], $source);
                }
            }
        }
        return $source;
    }

    /**
     * Очищает текст от BB-кодов
     *
     * @param  string $source неочищенный текст
     * @return string         очищенный текст
     */
    public function clear($source): string
    {
        return preg_replace('/\[(.*?)\]/', '', $source);
    }

    /**
     * Обрабатывает ссылки
     *
     * @param  array  $match ссылка
     * @return string        обработанная ссылка
     */
    public function urlReplace($match): string
    {
        $name = $match[3] ?? $match[1];

        $target = '';
        if ($match[2] !== '/') {
            if (strpos($match[1], $_SERVER['SERVER_NAME']) === false) {
                $target = ' target="_blank" rel="nofollow"';
            } else {
                $match[1] = '//' . ltrim($match[1], $match[2]);
            }
        }

        return '<a href="' . $match[1] . '"' . $target . '>' . rawurldecode($name) . '</a>';
    }

    /**
     * Обрабатывет списки
     *
     * @param  array  $match список
     * @return string обработанный список
     */
    public function listReplace($match): string
    {
        $li = preg_split('/<br[^>]*>\R/', $match[1], -1, PREG_SPLIT_NO_EMPTY);

        if (empty($li)) {
            return $match[0];
        }

        $list = [];
        foreach($li as $l) {
            $list[] = '<li>' . $l . '</li>';
        }

        $tag  = strpos($match[0], '[list]') === false ? 'ol' : 'ul';

        return '<' . $tag . '>' . implode($list) . '</' . $tag . '>';
    }

    /**
     * Обрабатывает размер текста
     *
     * @param  callable $match массив элементов
     * @return string   обработанный текст
     */
    public function fontSize($match): string
    {
        $sizes = [1 => 'x-small', 2 => 'small', 3 => 'medium', 4 => 'large', 5 => 'x-large'];

        return '<span style="font-size:' . $sizes[$match[1]] . '">' . $match[2] . '</span>';
    }

    /**
     * Подсвечивает код
     *
     * @param  callable $match массив элементов
     * @return string          текст с подсветкой
     */
    public function highlightCode($match): string
    {
        //Чтобы bb-код и стикеры не работали внутри тега [code]
        $match[1] = strtr($match[1], [':' => '&#58;', '[' => '&#91;']);

        return '<pre class="prettyprint linenums pre-scrollable">' . $match[1] . '</pre>';
    }

    /**
     * Скрывает текст под спойлер
     *
     * @param  callable $match массив элементов
     * @return string          код спойлера
     */
    public function spoilerText($match): string
    {
        $title = empty($match[2]) ? __('main.expand_view') : $match[1];
        $text  = empty($match[2]) ? $match[1] : $match[2];

        return '<div class="spoiler">
                <b class="spoiler-title">' . $title . '</b>
                <div class="spoiler-text" style="display: none;">' . $text . '</div>
            </div>';
    }

    /**
     * Скрывате текст от неавторизованных пользователей
     *
     * @param  callable $match массив элементов
     * @return string          скрытый код
     */
    public function hiddenText($match): string
    {
        return '<div class="hiding">
                <span class="font-weight-bold">' . __('main.hidden_content') . ':</span> ' .
                (getUser() ? $match[1] : __('main.not_authorized')) .
            '</div>';
    }

    /**
     * Обрабатывет стикеры
     *
     * @param $source
     * @return string Обработанный текст
     */
    public function parseStickers($source): string
    {
        static $listStickers;

        if (empty($listStickers)) {
            $listStickers = Cache::rememberForever('stickers', static function () {
                $stickers = Sticker::query()
                    ->select('code', 'name')
                    ->orderByDesc(DB::connection()->raw('CHAR_LENGTH(code)'))
                    ->get()
                    ->toArray();

                $stickers = array_column($stickers, 'name', 'code');

                return array_map(
                    static function($sticker) {
                        return '<img src="' . $sticker . '" alt="' . basename($sticker) . '">';
                    }, $stickers
                );
            });
        }

        return strtr($source, $listStickers);
    }

    /**
     * Добавляет или переопределяет парсер
     *
     * @param  string $name    Parser name
     * @param  string $pattern Pattern
     * @param  string $replace Replace pattern
     * @return void
     */
    public function setParser($name, $pattern, $replace): void
    {
        self::$parsers[$name] = [
            'pattern' => $pattern,
            'replace' => $replace
        ];
    }

    /**
     * Устанавливает список доступных парсеров
     *
     * @param  mixed  $only parsers
     * @return object BBCode object
     */
    public function only($only = null)
    {
        $only = is_array($only) ? $only : func_get_args();
        self::$parsers = $this->arrayOnly($only);
        return $this;
    }

    /**
     * Исключает парсеры из набора
     *
     * @param  mixed  $except parsers
     * @return object BBCode object
     */
    public function except($except = null)
    {
        $except = is_array($except) ? $except : func_get_args();
        self::$parsers = $this->arrayExcept($except);
        return $this;
    }

    /**
     * Возвращает список всех парсеров
     *
     * @return array array of parsers
     */
    public function getParsers(): array
    {
        return self::$parsers;
    }

    /**
     * Filters all parsers that you don´t want
     *
     * @param  array $only chosen parsers
     * @return array parsers
     */
    private function arrayOnly(array $only): array
    {
        return array_intersect_key(self::$parsers, array_flip($only));
    }

    /**
     * Removes the parsers that you don´t want
     *
     * @param array $excepts
     * @return array parsers
     */
    private function arrayExcept(array $excepts): array
    {
        return array_diff_key(self::$parsers, array_flip($excepts));
    }
}
