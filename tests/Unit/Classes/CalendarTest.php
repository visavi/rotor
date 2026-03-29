<?php

namespace Tests\Unit\Classes;

use App\Classes\Calendar;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Calendar::class)]
class CalendarTest extends TestCase
{
    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calendar = new Calendar();
    }

    public function testMakeCalendar(): void
    {
        $grid = $this->callMethod($this->calendar, 'makeCalendar', [1, 1980]);

        self::assertIsArray($grid);
        self::assertCount(5, $grid);
        self::assertNull($grid[0][0]);
        self::assertSame(1, $grid[0][1]);
        self::assertNull($grid[4][5]);
    }

    public function testGetCalendar(): void
    {
        $expected = '<divclass="fw-bold">1Января1980</div><divclass="calendar-grid"><divclass="calendar-headtext-center">Пн</div><divclass="calendar-headtext-center">Вт</div><divclass="calendar-headtext-center">Ср</div><divclass="calendar-headtext-center">Чт</div><divclass="calendar-headtext-center">Пт</div><divclass="calendar-headtext-centertext-danger">Сб</div><divclass="calendar-headtext-centertext-danger">Вс</div><divclass="calendar-celltext-center"></div><divclass="calendar-celltext-center"><spanclass="text-whitebg-dangerpx-1fw-bold">1</span></div><divclass="calendar-celltext-center">2</div><divclass="calendar-celltext-center">3</div><divclass="calendar-celltext-center">4</div><divclass="calendar-celltext-centertext-danger">5</div><divclass="calendar-celltext-centertext-danger">6</div><divclass="calendar-celltext-center">7</div><divclass="calendar-celltext-center">8</div><divclass="calendar-celltext-center">9</div><divclass="calendar-celltext-center">10</div><divclass="calendar-celltext-center">11</div><divclass="calendar-celltext-centertext-danger">12</div><divclass="calendar-celltext-centertext-danger">13</div><divclass="calendar-celltext-center">14</div><divclass="calendar-celltext-center">15</div><divclass="calendar-celltext-center">16</div><divclass="calendar-celltext-center">17</div><divclass="calendar-celltext-center">18</div><divclass="calendar-celltext-centertext-danger">19</div><divclass="calendar-celltext-centertext-danger">20</div><divclass="calendar-celltext-center">21</div><divclass="calendar-celltext-center">22</div><divclass="calendar-celltext-center">23</div><divclass="calendar-celltext-center">24</div><divclass="calendar-celltext-center">25</div><divclass="calendar-celltext-centertext-danger">26</div><divclass="calendar-celltext-centertext-danger">27</div><divclass="calendar-celltext-center">28</div><divclass="calendar-celltext-center">29</div><divclass="calendar-celltext-center">30</div><divclass="calendar-celltext-center">31</div><divclass="calendar-celltext-center"></div><divclass="calendar-celltext-centertext-danger"></div><divclass="calendar-celltext-centertext-danger"></div></div>';

        $calendar = preg_replace('/\s+/', '', $this->calendar->getCalendar(315586800));

        self::assertSame($expected, $calendar);
    }
}
