<?php

namespace Tests\Unit\Classes;

use App\Classes\Calendar;

class CalendarTest extends \Tests\TestCase
{
    private Calendar $calendar;

    public function setUp(): void
    {
        parent::setUp();
        $this->calendar = new Calendar();
    }

    /**
     * Testing makeCalendar
     */
    public function testMakeCalendar(): void
    {
        $makeCalendar = $this->callMethod($this->calendar, 'makeCalendar', [1, 1980]);

        self::assertIsArray($makeCalendar);
        self::assertCount(5, $makeCalendar);
        self::assertNull($makeCalendar[4][5]);
        self::assertNull($makeCalendar[0][0]);
        self::assertSame(1, $makeCalendar[0][1]);
    }

    /**
     * Testing getCalendar
     */
    public function testGetCalendar(): void
    {
        $expected = file_get_contents('tests/_data/calendar.txt');
        $calendar = preg_replace('/\s+/', '', $this->calendar->getCalendar(315586800));

        self::assertSame(trim($expected), $calendar);
    }
}
