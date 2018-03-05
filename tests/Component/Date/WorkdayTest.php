<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Date;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class WorkdayTest
 *
 * @package Ansas\Component\Date
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class WorkdayTest extends TestCase
{
    public function testCreate()
    {
        $date = new Workday();
        $this->assertInstanceOf(Workday::class, $date);
        $this->assertInstanceOf(DateTime::class, $date);

        $date = new Workday("now");
        $this->assertInstanceOf(Workday::class, $date);
        $this->assertInstanceOf(DateTime::class, $date);
    }

    public function testEqualsBetweenDateTimeAndWorkdayWorks()
    {
        $this->assertEquals(new DateTime("today"), new Workday("today"));
        $this->assertTrue(new DateTime("yesterday") < new DateTime("today"));
        $this->assertTrue(new DateTime("yesterday") < new Workday("today"));
        $this->assertTrue(new Workday("yesterday") < new DateTime("today"));
        $this->assertTrue(new Workday("yesterday") < new Workday("today"));
    }

    public function testCreateFromDateTime()
    {
        $date = Workday::fromDateTime(new DateTime());
        $this->assertInstanceOf(Workday::class, $date);
    }

    public function testIsHoliday()
    {
        $date = $this->createWorkdayIn2017("2017-04-30");
        $this->assertEquals(false, $date->isHoliday(), $date->format($date::DAY_DATE_FORMAT));

        $date->modify("+1 day");
        $this->assertEquals(true, $date->isHoliday(), $date->format($date::DAY_DATE_FORMAT));
    }

    public function testIsWeekend()
    {
        $date = new Workday();
        for ($i = 0; $i < 7; $i++) {
            $isWeekend = in_array($date->format($date::DAY_WEEKDAY_FORMAT), $date->getWeekendDays());
            $this->assertEquals($isWeekend, $date->isWeekend());
        }
    }

    public function testIsWorkday()
    {
        $date = $this->createWorkdayIn2017("2017-04-30");
        $this->assertEquals(false, $date->isWorkday(), "Weekend");

        $date->modify("+1 day");
        $this->assertEquals(false, $date->isWorkday(), "Holiday");

        $date->modify("+1 day");
        $this->assertEquals(true, $date->isWorkday(), "Workday");
    }

    public function testAddWorkdays()
    {
        $date = $this->createWorkdayIn2017("2017-04-28");
        $date->addWorkdays(1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-02");

        $date->addWorkdays(2);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-04");

        $date->addWorkdays(5);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-11");
    }

    /**
     * @param string $date
     *
     * @return Workday
     */
    public function createWorkdayIn2017($date)
    {
        $workday = new Workday($date);

        $workday->setHolidays([
            "2017-01-01" => "Neujahrstag",
            "2017-04-14" => "Karfreitag",
            "2017-04-17" => "Ostermontag",
            "2017-05-01" => "Tag der Arbeit",
            "2017-05-25" => "Christi Himmelfahrt",
            "2017-06-05" => "Pfingstmontag",
            "2017-10-03" => "Tag der Deutschen Einheit",
            "2017-10-31" => "Reformationstag",
            "2017-12-25" => "1. Weihnachtstag",
            "2017-12-26" => "2. Weihnachtstag",
        ]);

        return $workday;
    }
}