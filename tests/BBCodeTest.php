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
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<pre class="prettyprint linenums"><?php var_dump(&#91;1,2,4]);</pre>');
    }

    /**
     * Тестирует жирность текста
     */
    public function testBold()
    {
        $text      = '[b]Привет[/b]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<strong>Привет</strong>');
    }

    /**
     * Тестирует наклон текста
     */
    public function testItalic()
    {
        $text      = '[i]Привет[/i]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<em>Привет</em>');
    }

    /**
     * Тестирует подчеркивание текста
     */
    public function testUnderLine()
    {
        $text      = '[u]Привет[/u]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<u>Привет</u>');
    }

    /**
     * Тестирует зачеркивание текста
     */
    public function testLineThrough()
    {
        $text      = '[s]Привет[/s]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<del>Привет</del>');
    }

    /**
     * Тестирует размер текста
     */
    public function testFontSize()
    {
        $text      = '[size=5]Привет[/size]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<span style="font-size:x-large">Привет</span>');
    }

    /**
     * Тестирует цвет текста
     */
    public function testFontColor()
    {
        $text      = '[color=#ff0000]Привет[/color]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">Привет</span>');
    }

    /**
     * Тестирует вложенность цветов текста
     */
    public function testIterateFontColor()
    {
        $text      = '[color=#ff0000]П[color=#00ff00]р[color=#0000ff]и[/color][color=#00ff00]в[/color][/color]ет[/color]';
        $parseText = App::bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">П<span style="color:#00ff00">р<span style="color:#0000ff">и</span><span style="color:#00ff00">в</span></span>ет</span>');
    }
}
