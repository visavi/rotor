<?php

use PHPUnit\Framework\TestCase;

class BBCodeTest extends TestCase
{
    /**
     * Тестирует подсветку текста
     */
    public function testCode()
    {
        $text      = '[code]<?php var_dump([1,2,4]);[/code]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<pre class="prettyprint linenums pre-scrollable"><?php var_dump(&#91;1,2,4]);</pre>');
    }

    /**
     * Тестирует жирность текста
     */
    public function testBold()
    {
        $text      = '[b]Привет[/b]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<strong>Привет</strong>');
    }

    /**
     * Тестирует наклон текста
     */
    public function testItalic()
    {
        $text      = '[i]Привет[/i]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<em>Привет</em>');
    }

    /**
     * Тестирует подчеркивание текста
     */
    public function testUnderLine()
    {
        $text      = '[u]Привет[/u]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<u>Привет</u>');
    }

    /**
     * Тестирует зачеркивание текста
     */
    public function testLineThrough()
    {
        $text      = '[s]Привет[/s]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<del>Привет</del>');
    }

    /**
     * Тестирует размер текста
     */
    public function testFontSize()
    {
        $text      = '[size=5]Привет[/size]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="font-size:x-large">Привет</span>');
    }

    /**
     * Тестирует цвет текста
     */
    public function testFontColor()
    {
        $text      = '[color=#ff0000]Привет[/color]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">Привет</span>');
    }

    /**
     * Тестирует вложенность цветов текста
     */
    public function testIterateFontColor()
    {
        $text      = '[color=#ff0000]П[color=#00ff00]р[color=#0000ff]и[/color][color=#00ff00]в[/color][/color]ет[/color]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">П<span style="color:#00ff00">р<span style="color:#0000ff">и</span><span style="color:#00ff00">в</span></span>ет</span>');
    }

    /**
     * Тестирует центрирование текста
     */
    public function testCenter()
    {
        $text      = '[center]Привет[/center]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<div style="text-align:center;">Привет</div>');
    }

    /**
     * Тестирует цитирование текста
     */
    public function testQuote()
    {
        $text      = '[quote]Привет[/quote]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<blockquote>Привет</blockquote>');
    }

    /**
     * Тестирует цитирование текста с именем
     */
    public function testNamedQuote()
    {
        $text      = '[quote=Имя]Привет[/quote]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<blockquote>Привет<small>Имя</small></blockquote>');
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testHttp()
    {
        $text      = 'http://сайт.рф';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="http://сайт.рф" target="_blank" rel="nofollow">http://сайт.рф</a>');
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpNotTarget()
    {
        $text      = 'http://rotor.ll';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll">http://rotor.ll</a>');
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpsComplex()
    {
        $text      = 'https://rotor.ll/dir/index.php?name=name&name2=name2#anchor';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll/dir/index.php?name=name&name2=name2#anchor">https://rotor.ll/dir/index.php?name=name&name2=name2#anchor</a>');
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testLink()
    {
        $text      = '[url]https://rotor.ll[/url]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll">https://rotor.ll</a>');
    }

    /**
     * Тестирует именованную ссылку в тексте
     */
    public function testNamedLink()
    {
        $text      = '[url=http://rotor.ll/dir/index.php?name=name&name2=name2#anchor]Сайт[/url]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll/dir/index.php?name=name&name2=name2#anchor">Сайт</a>');
    }

    /**
     * Тестирует картинку в тексте
     */
    public function testImage()
    {
        $text      = '[img]http://rotor.ll/assets/images/img/logo.png[/img]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<img src="http://rotor.ll/assets/images/img/logo.png" class="img-fluid" alt="image">');
    }

    /**
     * Тестирует сортированный список в тексте
     */
    public function testOrderedList()
    {
        $text      = '[list=1]Список'.PHP_EOL.'список2[/list]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<ol><li>Список</li><li>список2</li></ol>');
    }

    /**
     * Тестирует несортированный список в тексте
     */
    public function testUnorderedList()
    {
        $text      = '[list]Список[/list]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<ul><li>Список</li></ul>');
    }

    /**
     * Тестирует спойлер в тексте
     */
    public function testSpoiler()
    {
        $text      = '[spoiler]Спойлер[/spoiler]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="spoiler"><b class="spoiler-title">Развернуть для просмотра</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>');
    }

    /**
     * Тестирует именованный спойлер в тексте
     */
    public function testShortSpoiler()
    {
        $text      = '[spoiler=Открыть]Спойлер[/spoiler]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="spoiler"><b class="spoiler-title">Открыть</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>');
    }

    /**
     * Тестирует скрытый текст
     */
    public function testHide()
    {
        $text      = '[hide]Скрытый текст[/hide]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="hiding"><span class="strong">Скрытый контент:</span> Для просмотра необходимо авторизоваться!</div>');
    }

    /**
     * Тестирует видео в тексте
     */
    public function testYoutube()
    {
        $text      = '[youtube]85bkCmaOh4o[/youtube]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//www.youtube.com/embed/85bkCmaOh4o"></iframe></div>');
    }

    /**
     * Тестирует смайлы в тексте
     */
    public function testSmile()
    {
        $text      = 'Привет :D :hello';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, 'Привет <img src="/uploads/smiles/D.gif" alt="D.gif"> <img src="/uploads/smiles/hello.gif" alt="hello.gif">');
    }

    /**
     * Тестирует очистку тегов в тексте
     */
    public function testClear()
    {
        $text      = '[center][b]Привет[/b] [i]Привет[/i][/center]';
        $parseText = bbCode($text, false);

        $this->assertEquals($parseText, 'Привет Привет');
    }
}
