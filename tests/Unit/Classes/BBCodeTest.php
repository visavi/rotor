<?php

namespace Tests\Unit\Classes;

use App\Classes\BBCode;
use App\Models\Sticker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BBCode::class)]
class BBCodeTest extends TestCase
{
    use RefreshDatabase;

    private BBCode $bbCode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bbCode = new BBCode();
    }

    public function testParse(): void
    {
        // formatting tags
        self::assertSame('<strong>Привет</strong>', $this->bbCode->parse('[b]Привет[/b]'));
        self::assertSame('<em>Привет</em>', $this->bbCode->parse('[i]Привет[/i]'));
        self::assertSame('<u>Привет</u>', $this->bbCode->parse('[u]Привет[/u]'));
        self::assertSame('<del>Привет</del>', $this->bbCode->parse('[s]Привет[/s]'));

        // code
        self::assertSame(
            '<pre class="prettyprint"><?php var_dump(&#91;1,2,4]);</pre>',
            $this->bbCode->parse('[code]<?php var_dump([1,2,4]);[/code]')
        );

        // font size
        self::assertSame(
            '<span style="font-size:x-large">Привет</span>',
            $this->bbCode->parse('[size=5]Привет[/size]')
        );

        // font color
        self::assertSame(
            '<span style="color:#ff0000">Привет</span>',
            $this->bbCode->parse('[color=#ff0000]Привет[/color]')
        );

        // nested colors
        $input = '[color=#ff0000]П[color=#00ff00]р[color=#0000ff]и[/color][color=#00ff00]в[/color][/color]ет[/color]';
        $expected = '<span style="color:#ff0000">П<span style="color:#00ff00">р<span style="color:#0000ff">и</span><span style="color:#00ff00">в</span></span>ет</span>';
        self::assertSame($expected, $this->bbCode->parse($input));

        // center
        self::assertSame(
            '<div style="text-align:center;">Привет</div>',
            $this->bbCode->parse('[center]Привет[/center]')
        );

        // quote
        self::assertSame(
            '<blockquote class="blockquote">Привет</blockquote>',
            $this->bbCode->parse('[quote]Привет[/quote]')
        );

        // named quote
        self::assertSame(
            '<blockquote class="blockquote">Привет<footer class="blockquote-footer">Имя</footer></blockquote>',
            $this->bbCode->parse('[quote=Имя]Привет[/quote]')
        );

        // external url
        $input = 'http://сайт.рф http://сайт.рф/http://сайт.рф:80';
        $expected = '<a href="http://сайт.рф" target="_blank" rel="nofollow">http://сайт.рф</a> <a href="http://сайт.рф/http://сайт.рф:80" target="_blank" rel="nofollow">http://сайт.рф/http://сайт.рф:80</a>';
        self::assertSame($expected, $this->bbCode->parse($input));

        // internal url
        $url = config('app.url');
        $cutUrl = str_replace(['http:', 'https:'], '', $url);
        self::assertSame('<a href="' . $cutUrl . '">' . $url . '</a>', $this->bbCode->parse($url));

        // complex internal url
        $url = config('app.url') . '/dir/index.php?name=name&name2=name2#anchor';
        $cutUrl = str_replace(['http:', 'https:'], '', $url);
        self::assertSame('<a href="' . $cutUrl . '">' . $url . '</a>', $this->bbCode->parse($url));

        // [url] tag
        self::assertSame(
            '<a href="https://site.ru" target="_blank" rel="nofollow">https://site.ru</a>',
            $this->bbCode->parse('[url]https://site.ru[/url]')
        );

        // named [url] tag
        $input = '[url=https://site.ru/dir/index.php?name=name&name2=name2#anchor]Сайт[/url] [url=https://site.com/http://site.net:80/]Sitename[/url]';
        $expected = '<a href="https://site.ru/dir/index.php?name=name&name2=name2#anchor" target="_blank" rel="nofollow">Сайт</a> <a href="https://site.com/http://site.net:80/" target="_blank" rel="nofollow">Sitename</a>';
        self::assertSame($expected, $this->bbCode->parse($input));

        // [img] tag
        self::assertSame(
            '<div class="media-file"><a href="https://visavi.net/assets/images/img/logo.png" data-fancybox="gallery"><img src="https://visavi.net/assets/images/img/logo.png" class="img-fluid" alt="image"></a></div>',
            $this->bbCode->parse('[img]https://visavi.net/assets/images/img/logo.png[/img]')
        );

        // ordered list
        self::assertSame(
            '<ol><li>Список</li><li>список2</li></ol>',
            $this->bbCode->parse('[list=1]Список' . PHP_EOL . 'список2[/list]')
        );

        // unordered list
        self::assertSame(
            '<ul><li>Список</li></ul>',
            $this->bbCode->parse('[list]Список[/list]')
        );

        // spoiler
        $result = trim(preg_replace('/\s\s+/', '', $this->bbCode->parse('[spoiler]Спойлер[/spoiler]')));
        self::assertSame(
            '<div class="spoiler"><b class="spoiler-title">' . __('main.expand_view') . '</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>',
            $result
        );

        // named spoiler
        $result = trim(preg_replace('/\s\s+/', '', $this->bbCode->parse('[spoiler=Открыть]Спойлер[/spoiler]')));
        self::assertSame(
            '<div class="spoiler"><b class="spoiler-title">Открыть</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>',
            $result
        );

        // hidden text
        $result = trim(preg_replace('/\s\s+/', '', $this->bbCode->parse('[hide]Скрытый текст[/hide]')));
        self::assertSame(
            '<div class="hidden-text"><span class="fw-bold">' . __('main.hidden_content') . ':</span> ' . __('main.not_authorized') . '</div>',
            $result
        );

        // youtube
        self::assertSame(
            '<div class="media-file ratio ratio-16x9"><iframe src="//www.youtube.com/embed/85bkCmaOh4o" allowfullscreen></iframe></div>',
            $this->bbCode->parse('[youtube]https://www.youtube.com/watch?v=85bkCmaOh4o[/youtube]')
        );
    }

    public function testParseStickers(): void
    {
        Sticker::query()->insert([
            ['code' => ':D',     'name' => '/uploads/stickers/D.gif',     'category_id' => 0],
            ['code' => ':hello', 'name' => '/uploads/stickers/hello.gif', 'category_id' => 0],
        ]);

        Cache::forget('stickers');

        self::assertSame(
            'Привет <img src="/uploads/stickers/D.gif" alt="D"> <img src="/uploads/stickers/hello.gif" alt="hello">',
            $this->bbCode->parseStickers('Привет :D :hello')
        );
    }

    public function testClear(): void
    {
        self::assertSame(
            'Привет Привет',
            $this->bbCode->clear('[center][b]Привет[/b] [i]Привет[/i][/center]')
        );
    }
}
