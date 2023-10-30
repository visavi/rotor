<?php

namespace Tests\Unit\Classes;

use App\Classes\BBCode;

class BBCodeTest extends \Tests\TestCase
{
    /**
     * @var BBCode
     */
    private $bbCode;

    public function setUp(): void
    {
        parent::setUp();
        $this->bbCode = new BBCode();
    }

    /**
     * Тестирует подсветку текста
     */
    public function testCode(): void
    {
        $text      = '[code]<?php var_dump([1,2,4]);[/code]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<pre class="prettyprint"><?php var_dump(&#91;1,2,4]);</pre>', $parseText);
    }

    /**
     * Тестирует жирность текста
     */
    public function testBold(): void
    {
        $text      = '[b]Привет[/b]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<strong>Привет</strong>', $parseText);
    }

    /**
     * Тестирует наклон текста
     */
    public function testItalic(): void
    {
        $text      = '[i]Привет[/i]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<em>Привет</em>', $parseText);
    }

    /**
     * Тестирует подчеркивание текста
     */
    public function testUnderLine(): void
    {
        $text      = '[u]Привет[/u]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<u>Привет</u>', $parseText);
    }

    /**
     * Тестирует зачеркивание текста
     */
    public function testLineThrough(): void
    {
        $text      = '[s]Привет[/s]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<del>Привет</del>', $parseText);
    }

    /**
     * Тестирует размер текста
     */
    public function testFontSize(): void
    {
        $text      = '[size=5]Привет[/size]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<span style="font-size:x-large">Привет</span>', $parseText);
    }

    /**
     * Тестирует цвет текста
     */
    public function testFontColor(): void
    {
        $text      = '[color=#ff0000]Привет[/color]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<span style="color:#ff0000">Привет</span>', $parseText);
    }

    /**
     * Тестирует вложенность цветов текста
     */
    public function testIterateFontColor(): void
    {
        $text      = '[color=#ff0000]П[color=#00ff00]р[color=#0000ff]и[/color][color=#00ff00]в[/color][/color]ет[/color]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<span style="color:#ff0000">П<span style="color:#00ff00">р<span style="color:#0000ff">и</span><span style="color:#00ff00">в</span></span>ет</span>', $parseText);
    }

    /**
     * Тестирует центрирование текста
     */
    public function testCenter(): void
    {
        $text      = '[center]Привет[/center]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<div style="text-align:center;">Привет</div>', $parseText);
    }

    /**
     * Тестирует цитирование текста
     */
    public function testQuote(): void
    {
        $text      = '[quote]Привет[/quote]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<blockquote class="blockquote">Привет</blockquote>', $parseText);
    }

    /**
     * Тестирует цитирование текста с именем
     */
    public function testNamedQuote(): void
    {
        $text      = '[quote=Имя]Привет[/quote]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<blockquote class="blockquote">Привет<footer class="blockquote-footer">Имя</footer></blockquote>', $parseText);
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testHttp(): void
    {
        $text      = 'http://сайт.рф http://сайт.рф/http://сайт.рф:80';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<a href="http://сайт.рф" target="_blank" rel="nofollow">http://сайт.рф</a> <a href="http://сайт.рф/http://сайт.рф:80" target="_blank" rel="nofollow">http://сайт.рф/http://сайт.рф:80</a>', $parseText);
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpNotTarget(): void
    {
        $url    = config('app.url');
        $cutUrl = str_replace(['http:', 'https:'], '', $url);
        $parseText = $this->bbCode->parse($url);

        self::assertSame('<a href="' . $cutUrl . '">' . $url . '</a>', $parseText);
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpsComplex(): void
    {
        $url    = config('app.url') . '/dir/index.php?name=name&name2=name2#anchor';
        $cutUrl = str_replace(['http:', 'https:'], '', $url);
        $parseText = $this->bbCode->parse($url);

        self::assertSame('<a href="' . $cutUrl . '">' . $url . '</a>', $parseText);
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testLink(): void
    {
        $text      = '[url]https://site.ru[/url]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<a href="https://site.ru" target="_blank" rel="nofollow">https://site.ru</a>', $parseText);
    }

    /**
     * Тестирует именованную ссылку в тексте
     */
    public function testNamedLink(): void
    {
        $text      = '[url=https://site.ru/dir/index.php?name=name&name2=name2#anchor]Сайт[/url] [url=https://site.com/http://site.net:80/]Sitename[/url]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<a href="https://site.ru/dir/index.php?name=name&name2=name2#anchor" target="_blank" rel="nofollow">Сайт</a> <a href="https://site.com/http://site.net:80/" target="_blank" rel="nofollow">Sitename</a>', $parseText);
    }

    /**
     * Тестирует картинку в тексте
     */
    public function testImage(): void
    {
        $text      = '[img]https://visavi.net/assets/images/img/logo.png[/img]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<div class="media-file"><a href="https://visavi.net/assets/images/img/logo.png" data-fancybox="gallery"><img src="https://visavi.net/assets/images/img/logo.png" class="img-fluid" alt="image"></a></div>', $parseText);
    }

    /**
     * Тестирует сортированный список в тексте
     */
    public function testOrderedList(): void
    {
        $text      = '[list=1]Список' . PHP_EOL . 'список2[/list]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<ol><li>Список</li><li>список2</li></ol>', $parseText);
    }

    /**
     * Тестирует несортированный список в тексте
     */
    public function testUnorderedList(): void
    {
        $text      = '[list]Список[/list]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<ul><li>Список</li></ul>', $parseText);
    }

    /**
     * Тестирует спойлер в тексте
     */
    public function testSpoiler(): void
    {
        $text      = '[spoiler]Спойлер[/spoiler]';
        $parseText = $this->bbCode->parse($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        self::assertSame('<div class="spoiler"><b class="spoiler-title">' . __('main.expand_view') . '</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>', $parseText);
    }

    /**
     * Тестирует именованный спойлер в тексте
     */
    public function testShortSpoiler(): void
    {
        $text      = '[spoiler=Открыть]Спойлер[/spoiler]';
        $parseText = $this->bbCode->parse($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        self::assertSame('<div class="spoiler"><b class="spoiler-title">Открыть</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>', $parseText);
    }

    /**
     * Тестирует скрытый текст
     */
    public function testHide(): void
    {
        $text      = '[hide]Скрытый текст[/hide]';
        $parseText = $this->bbCode->parse($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        self::assertSame('<div class="hidden-text"><span class="fw-bold">' . __('main.hidden_content') . ':</span> ' . __('main.not_authorized') . '</div>', $parseText);
    }

    /**
     * Тестирует видео в тексте
     */
    public function testYoutube(): void
    {
        $text      = '[youtube]https://www.youtube.com/watch?v=85bkCmaOh4o[/youtube]';
        $parseText = $this->bbCode->parse($text);

        self::assertSame('<div class="media-file ratio ratio-16x9"><iframe src="//www.youtube.com/embed/85bkCmaOh4o" allowfullscreen></iframe></div>', $parseText);
    }

    /**
     * Тестирует стикеры в тексте
     */
    public function testSticker(): void
    {
        $text      = 'Привет :D :hello';
        $parseText = $this->bbCode->parseStickers($text);

        self::assertSame('Привет <img src="/uploads/stickers/D.gif" alt="D"> <img src="/uploads/stickers/hello.gif" alt="hello">', $parseText);
    }

    /**
     * Тестирует очистку тегов в тексте
     */
    public function testClear(): void
    {
        $text      = '[center][b]Привет[/b] [i]Привет[/i][/center]';
        $parseText = $this->bbCode->clear($text);

        self::assertSame('Привет Привет', $parseText);
    }
}
