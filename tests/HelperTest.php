<?php

use PHPUnit\Framework\TestCase;
use App\Models\Guestbook;

class HelperTest extends TestCase
{
    public function testMakeTime(): void
    {
        $makeTime = makeTime(100);
        $this->assertSame($makeTime, '01:40');

        $makeTime = makeTime(5000);
        $this->assertSame($makeTime, '01:23:20');
    }

    public function testDateFixed(): void
    {
        $timestamp = 1117627200;

        $this->assertSame(dateFixed($timestamp), '01.06.05 / 12:00');

        $this->assertSame(dateFixed($timestamp, 'Y-m-d'), '2005-06-01');

        $this->assertSame(dateFixed(false), dateFixed(SITETIME));
    }

    public function testFormatTime(): void
    {
        $formatTime = formatTime(0);
        $this->assertSame($formatTime, 0);

        $formatTime = formatTime(60);
        $this->assertSame($formatTime, '1 минута');

        $formatTime = formatTime(3600);
        $this->assertSame($formatTime, '1 час');

        $formatTime = formatTime(86400);
        $this->assertSame($formatTime, '1 день');

        $formatTime = formatTime(86400 * 365);
        $this->assertSame($formatTime, '1 год');
    }
}
