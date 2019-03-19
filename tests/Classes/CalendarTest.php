<?php

use App\Classes\Calendar;

class CalendarTest extends \Tests\TestCase
{
    /**
     * @var calendar
     */
    private $calendar;

    public function setUp()
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

        $this->assertIsArray($makeCalendar);
        $this->assertCount(5, $makeCalendar);
        $this->assertNull($makeCalendar[4][5]);
        $this->assertNull($makeCalendar[0][0]);
        $this->assertSame(1, $makeCalendar[0][1]);
    }

    /**
     * Testing getCalendar
     */
    public function testGetCalendar(): void
    {
        $expected = file_get_contents('tests/_data/calendar.txt');
        $calendar = preg_replace('/\s+/', '', $this->calendar->getCalendar(315586800));

        $this->assertSame(trim($expected), $calendar);
    }
}
