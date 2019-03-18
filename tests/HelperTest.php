<?php

use App\Models\Antimat;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * Testing makeTime
     */
    public function testMakeTime(): void
    {
        $makeTime = makeTime(100);
        $this->assertSame('01:40', $makeTime);

        $makeTime = makeTime(5000);
        $this->assertSame('01:23:20', $makeTime);
    }

    /**
     * Testing dateFixed
     */
    public function testDateFixed(): void
    {
        $timestamp = 1117627200;

        $this->assertSame('01.06.05 / 12:00', dateFixed($timestamp));
        $this->assertSame('2005-06-01', dateFixed($timestamp, 'Y-m-d'));
        $this->assertSame(dateFixed(SITETIME), dateFixed(false));
    }

    /**
     * Testing winToUtf
     */
    public function testWinToUtf(): void
    {
        $str = winToUtf('ABCDEFGHabcdefgh');
        $this->assertSame('ABCDEFGHabcdefgh', $str);

        $str = winToUtf(chr(192) . chr(193) . chr(194) . chr(195) . chr(196) . chr(197) . chr(168) . chr(224) . chr(225) . chr(226) . chr(227) . chr(228) . chr(229) . chr(184));
        $this->assertSame('АБВГДЕЁабвгдеё', $str);
    }

    /**
     * Testing utfLower
     */
    public function testUtfLower(): void
    {
        $this->assertSame('abcdefghabcdefgh', utfLower('ABCDEFGHabcdefgh'));
        $this->assertSame('абвгдеёабвгдеё', utfLower('АБВГДЕЁабвгдеё'));
    }

    /**
     * Testing utfSubstr
     */
    public function testUtfSubstr(): void
    {
        $this->assertSame('abc', utfSubstr('abcdefgh', 0, 3));
        $this->assertSame('абв', utfSubstr('абвгдеё', 0, 3));
        $this->assertSame('гдеё', utfSubstr('абвгдеё', 3, 4));
        $this->assertSame('ё', utfSubstr('абвгдеё', -1));
    }

    /**
     * Testing utfStrlen
     */
    public function testUtfStrlen(): void
    {
        $this->assertSame(8, utfStrlen('abcdefgh'));
        $this->assertSame(7, utfStrlen('абвгдеё'));
        $this->assertSame(1, utfStrlen(0));
        $this->assertSame(1, utfStrlen(true));
        $this->assertSame(0, utfStrlen(false));
        $this->assertSame(0, utfStrlen(''));
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
        $this->assertSame('&lt;br&gt;', check('<br>'));
        $this->assertSame('&amp;lt;br&amp;gt;', check('&lt;br&gt;'));
        $this->assertSame('&lt;br&gt;', check('&lt;br&gt;', false));
        $this->assertSame(['&lt;br&gt;', '&lt;html&gt;'], check(['<br>', '<html>']));
        $this->assertSame('', check(chr(0). "\x00". "\x1A". chr(226) . chr(128) . chr(174)));
        $this->assertSame('test', check(' test '));
    }

    /**
     * Testing int
     */
    public function testInt(): void
    {
        $this->assertSame(5, int(5));
        $this->assertSame(5, int('5'));
        $this->assertSame(0, int(false));
        $this->assertSame(1, int(true));
        $this->assertSame(1, int('1abc'));
        $this->assertSame(1, int(-1));
        $this->assertSame(1, int('-1'));
        $this->assertSame(0, int([]));
        $this->assertSame(0, int([1]));
        $this->assertSame(0, int(['abc']));
    }

    /**
     * Testing intar
     */
    public function testIntar(): void
    {
        $this->assertIsArray(intar(5));
        $this->assertSame([5], intar(5));
        $this->assertSame([5], intar('5'));
        $this->assertSame([0, 1, 33, -1, 0, 0], intar([false, true, '33abc', '-1', 0.01, 'abc']));
    }

    /**
     * Testing formatSize
     */
    public function testFormatSize(): void
    {
        $this->assertSame('5byte', formatSize(5));
        $this->assertSame('0.98Kb', formatSize(1000));
        $this->assertSame('1Kb', formatSize(1024));
        $this->assertSame('1Mb', formatSize(1048576));
        $this->assertSame('1Tb', formatSize(1099511627776));
        $this->assertSame('0.9766Kb', formatSize(1000, 4));
    }

    /**
     * Testing formatFileSize
     */
    public function testFormatFileSize(): void
    {
        $file = UploadedFile::fake()->create('test.txt');

        $this->assertSame('0byte', formatFileSize($file));
        $this->assertSame(0, formatFileSize(false));
    }

    /**
     * Testing formatTime
     */
    public function testFormatTime(): void
    {
        $formatTime = formatTime(0);
        $this->assertSame(0, $formatTime);

        $formatTime = formatTime(60);
        $this->assertSame('1 минута', $formatTime);

        $formatTime = formatTime(3600);
        $this->assertSame('1 час', $formatTime);

        $formatTime = formatTime(86400);
        $this->assertSame('1 день', $formatTime);

        $formatTime = formatTime(86400 * 365);
        $this->assertSame('1 год', $formatTime);
    }

    /**
     * Testing antimat
     */
    public function testAntimat(): void
    {
        $antimat = Antimat::query()->create([
            'string' => 'xxx',
        ]);

        $this->assertSame('test', antimat('test'));
        $this->assertSame('test***test', antimat('testxxxtest'));
        $this->assertSame('тест***тест***', antimat('тестxxxтестxxx'));

        $antimat->delete();
    }

    /**
     * Testing ratingVote
     */
    public function testRatingVote(): void
    {
        $this->assertSame('<div class="star-rating fa-lg text-danger"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>( 4 )</div>', ratingVote(4.2));

        $this->assertSame('<div class="star-rating fa-lg text-danger"><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>( 1.5 )</div>', ratingVote(1.5));
    }

    /**
     * Testing getCalendar
     */
    public function testGetCalendar(): void
    {
        $expected = file_get_contents('tests/_data/calendar.txt');
        $calendar = preg_replace('/\s+/', '', getCalendar(315586800));

        $this->assertSame(trim($expected), $calendar);

    }

}
