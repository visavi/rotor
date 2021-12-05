<?php

namespace Tests\Unit;

use App\Models\Antimat;
use Illuminate\Http\UploadedFile;

class HelperTest extends \Tests\TestCase
{
    /**
     * Testing makeTime
     */
    public function testMakeTime(): void
    {
        $makeTime = makeTime(100);
        self::assertSame('01:40', $makeTime);

        $makeTime = makeTime(5000);
        self::assertSame('01:23:20', $makeTime);
    }

    /**
     * Testing dateFixed
     */
    public function testDateFixed(): void
    {
        $timestamp = 1117612800;

        self::assertSame('01.06.2005 / 12:00', dateFixed($timestamp));
        self::assertSame('2005-06-01', dateFixed($timestamp, 'Y-m-d'));
        self::assertSame('1 Июня 2005', dateFixed($timestamp, 'j F Y'));
        self::assertSame('1 June 2005', dateFixed($timestamp, 'j F Y', true));
        self::assertSame(dateFixed(SITETIME), dateFixed(null));
    }

    /**
     * Testing winToUtf
     */
    public function testWinToUtf(): void
    {
        $str = winToUtf('ABCDEFGHabcdefgh');
        self::assertSame('ABCDEFGHabcdefgh', $str);

        $str = winToUtf(chr(192) . chr(193) . chr(194) . chr(195) . chr(196) . chr(197) . chr(168) . chr(224) . chr(225) . chr(226) . chr(227) . chr(228) . chr(229) . chr(184));
        self::assertSame('АБВГДЕЁабвгдеё', $str);
    }

    /**
     * Testing utfLower
     */
    public function testUtfLower(): void
    {
        self::assertSame('abcdefghabcdefgh', utfLower('ABCDEFGHabcdefgh'));
        self::assertSame('абвгдеёабвгдеё', utfLower('АБВГДЕЁабвгдеё'));
    }

    /**
     * Testing utfSubstr
     */
    public function testUtfSubstr(): void
    {
        self::assertSame('abc', utfSubstr('abcdefgh', 0, 3));
        self::assertSame('абв', utfSubstr('абвгдеё', 0, 3));
        self::assertSame('гдеё', utfSubstr('абвгдеё', 3, 4));
        self::assertSame('ё', utfSubstr('абвгдеё', -1));
    }

    /**
     * Testing utfStrlen
     */
    public function testUtfStrlen(): void
    {
        self::assertSame(8, utfStrlen('abcdefgh'));
        self::assertSame(7, utfStrlen('абвгдеё'));
        self::assertSame(1, utfStrlen('0'));
        self::assertSame(1, utfStrlen(true));
        self::assertSame(0, utfStrlen(false));
        self::assertSame(0, utfStrlen(''));
    }

    /**
     * Testing isUtf
     */
    public function testIsUtf(): void
    {
        $this->assertTrue(isUtf(''));
        $this->assertTrue(isUtf('абвгдеё'));
        $this->assertTrue(isUtf('0'));
        $this->assertTrue(isUtf(0));
        $this->assertTrue(isUtf(false));
        $this->assertTrue(isUtf('🤓 😃 😊'));
        $this->assertFalse(isUtf(chr(192) . chr(193) . chr(194)));
    }

    /**
     * Testing check
     */
    public function testCheck(): void
    {
        self::assertSame('&lt;br&gt;', check('<br>'));
        self::assertSame('&amp;lt;br&amp;gt;', check('&lt;br&gt;'));
        self::assertSame('&lt;br&gt;', check('&lt;br&gt;', false));
        self::assertSame(['&lt;br&gt;', '&lt;html&gt;'], check(['<br>', '<html>']));
        self::assertSame('', check(chr(0). "\x00". "\x1A". chr(226) . chr(128) . chr(174)));
        self::assertSame(' test ', check(' test '));
    }

    /**
     * Testing int
     */
    public function testInt(): void
    {
        self::assertSame(5, int(5));
        self::assertSame(5, int('5'));
        self::assertSame(0, int(false));
        self::assertSame(1, int(true));
        self::assertSame(1, int('1abc'));
        self::assertSame(1, int(-1));
        self::assertSame(1, int('-1'));
        self::assertSame(0, int([]));
        self::assertSame(1, int([1]));
    }

    /**
     * Testing intar
     */
    public function testIntar(): void
    {
        $this->assertIsArray(intar(5));
        self::assertSame([5], intar(5));
        self::assertSame([5], intar('5'));
        self::assertSame([0, 1, 33, -1, 0, 0], intar([false, true, '33abc', '-1', 0.01, 'abc']));
    }

    /**
     * Testing formatSize
     */
    public function testFormatSize(): void
    {
        self::assertSame('5B', formatSize(5));
        self::assertSame('0.98Kb', formatSize(1000));
        self::assertSame('1Kb', formatSize(1024));
        self::assertSame('1Mb', formatSize(1048576));
        self::assertSame('1Tb', formatSize(1099511627776));
        self::assertSame('0.9766Kb', formatSize(1000, 4));
    }

    /**
     * Testing formatFileSize
     */
    public function testFormatFileSize(): void
    {
        $file = UploadedFile::fake()->create('test.txt');

        self::assertSame('0B', formatFileSize($file->getPathname()));
        self::assertSame('0B', formatFileSize(false));
    }

    /**
     * Testing formatTime
     */
    public function testFormatTime(): void
    {
        $formatTime = formatTime(0);
        self::assertSame('0', $formatTime);

        $formatTime = formatTime(60);
        self::assertSame('1 минута', $formatTime);

        $formatTime = formatTime(3600);
        self::assertSame('1 час', $formatTime);

        $formatTime = formatTime(86400);
        self::assertSame('1 день', $formatTime);

        $formatTime = formatTime(86400 * 365);
        self::assertSame('1 год', $formatTime);
    }

    /**
     * Testing antimat
     */
    public function testAntimat(): void
    {
        $antimat = Antimat::query()->create([
            'string' => 'xxx',
        ]);

        self::assertSame('test', antimat('test'));
        self::assertSame('test***test', antimat('testxxxtest'));
        self::assertSame('тест***тест***', antimat('тестxxxтестxxx'));

        $antimat->delete();
    }

    /**
     * Testing bbCode
     */
    public function testBbCode(): void
    {
        self::assertSame('<strong>Hello</strong> <img src="/uploads/stickers/D.gif" alt="D"> &lt;br&gt; <u>world</u>', bbCode('[b]Hello[/b] :D <br> [u]world[/u]')->toHtml());

        self::assertSame('Hello :D world', bbCode('[b]Hello[/b] :D [u]world[/u]', false)->toHtml());
    }
}
