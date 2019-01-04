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
use Exception;
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
        $this->assertEquals(Workday::createFromFormat("!Y-m-d", "2018-03-18"), Workday::createFromFormat("!Y-m-d", "2018-03-18"));
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

        $date->addWorkdays(-5);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-04");
    }

    public function testAddWorkdaysIfTodayIsHoliday()
    {
        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->addWorkdays(1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->addWorkdays(-1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-13");
    }

    public function testNextLastWorkday()
    {
        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->nextWorkday();
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('next workday');
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->lastWorkday();
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-13");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('last workday');
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-13");

        $date = $this->createWorkdayIn2017("2017-04-18");
        $date->modify('last workday');
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");
        $date->modify('next workday');
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('last workday - 1 workdays');
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-12");
    }

    public function testAddWorkdaysIfTodayIsWeekend()
    {
        $date = $this->createWorkdayIn2017("2017-04-15");
        $date->addWorkdays(1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-15");
        $date->addWorkdays(-1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-04-13");
    }

    public function testModify()
    {
        $date = $this->createWorkdayIn2017("2017-05-24 10:00:00");
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");

        $date->modify('1 workday');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-26 10:00:00");

        $date->modify('-1 workdays');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");

        $date->modify('+1 workdays + 1 hour');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-26 11:00:00");

        $date->modify('- 1 hour - 1 workday');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");

        $date->modify('+ 1 hour + 1 day');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-05-25 11:00:00");
    }

    public function testClone()
    {
        $date1 = $this->createWorkdayIn2017("2017-05-24 10:00:00");
        $date2 = clone $date1;
        $date2->modify('+1 hour');

        $this->assertEquals($date1->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");
        $this->assertEquals($date2->format('Y-m-d H:i:s'), "2017-05-24 11:00:00");
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
