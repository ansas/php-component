<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */

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
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

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
        $this->assertEquals($date->getHolidayTemplate(), Workday::DEFAULT_TEMPLATE_NAME);

        $date = Workday::create("now");
        $this->assertInstanceOf(Workday::class, $date);
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertEquals($date->getHolidayTemplate(), Workday::DEFAULT_TEMPLATE_NAME);
    }

    /**
     * @expectedException Exception
     */
    public function testHolidaysPathNotSet()
    {
        $this->expectExceptionMessage('Call setHolidayPath() first');
        Workday::create()->getHolidays();
    }

    /**
     * @expectedException Exception
     */
    public function testHolidaysTemplateNotExists()
    {
        Workday::setHolidayPath(__DIR__ . '/../../var/component/date/workday');

        $this->expectExceptionMessage('Holiday template does not exist');
        $date = Workday::create()->setHolidayTemplate('unknown')->getHolidays();
    }

    public function testGetHolidayNext()
    {
        $date = $this->createWorkdayIn2017("2017-01-01")->getHolidayNext();
        $this->assertSame('2017-01-01', $date->getDateFormatted());

        $date = $this->createWorkdayIn2017("2017-01-02")->getHolidayNext();
        $this->assertSame('2017-04-14', $date->getDateFormatted());

        $date = $this->createWorkdayIn2017("2017-12-24")->setTime(23,59,59)->getHolidayNext();
        $this->assertSame('2017-12-25', $date->getDateFormatted());
    }

    public function testGetHolidayName()
    {
        $date = $this->createWorkdayIn2017("2017-04-03");
        $this->assertNull($date->getHolidayName());

        $this->assertSame('Karfreitag', $date->getHolidayNext()->getHolidayName());
    }

    /**
     * @expectedException Exception
     */
    public function testHolidaysTemplateNotValid()
    {
        $this->expectExceptionMessage('Holiday template not valid');
        $date = Workday::create()->setHolidayTemplate('invalid')->getHolidays();
    }

    public function testGetHolidaysTemplateValid()
    {
        $date = Workday::create();
        $this->assertIsArray($date->getHolidays());
    }

    public function testEqualsBetweenDateTimeAndWorkdayWorks()
    {
        $this->assertEquals(new DateTime("today"), new Workday("today"));

        $this->assertEquals(
            Workday::createFromFormat("!Y-m-d", "2018-03-18"),
            Workday::createFromFormat("!Y-m-d", "2018-03-18")
        );

        $this->assertTrue(new DateTime("yesterday") < new DateTime("today"));
        $this->assertTrue(new DateTime("yesterday") < new Workday("today"));

        $this->assertTrue(new Workday("yesterday") < new DateTime("today"));
        $this->assertTrue(new Workday("yesterday") < new Workday("today"));
    }

    public function testEqualsWithTimezoneInDateString()
    {
        // Timezone in constructor must not be used
        foreach (['Z', '+00', '+0000', '+00:00'] as $suffix) {
            $date = new Workday('2018-04-30T22:30:00' . $suffix, new DateTimeZone('Europe/Paris'));
            $this->assertEquals($date->getOffset(), 0);
            $this->assertEquals($date->getTimezone()->getName(), 'UTC');
            $this->assertEquals($date->format('Y-m-d H:i:s'), "2018-04-30 22:30:00");
        }

        // Timezone in constructor must be used
        foreach (['Europe/Paris', 'UTC'] as $timezone) {
            $date = new Workday('2018-04-30T22:30:00', new DateTimeZone($timezone));
            $this->assertEquals($date->getTimezone()->getName(), $timezone);
            $this->assertEquals($date->format('Y-m-d H:i:s'), "2018-04-30 22:30:00");
        }
    }

    public function testCreateFromDateTime()
    {
        $timezone = new DateTimeZone('Europe/Paris');
        $date     = new DateTime('now', $timezone);
        $workday  = Workday::fromDateTime($date);
        $this->assertEquals(Workday::class, get_class($workday));
        $this->assertEquals($date->format(DATE_ATOM), $workday->format(DATE_ATOM));
        $this->assertEquals($timezone->getName(), $workday->getTimezone()->getName());
    }

    public function testToDateTime()
    {
        $workday = new Workday('now', new DateTimeZone('Europe/Paris'));
        $date    = $workday->toDateTime();

        $this->assertEquals(DateTime::class, get_class($date));
        $this->assertEquals($date->format(DATE_ATOM), $workday->format(DATE_ATOM));
        $this->assertEquals($date->getTimezone()->getName(), $workday->getTimezone()->getName());
    }

    public function testIsHoliday()
    {
        $date = $this->createWorkdayIn2017("2017-04-30");
        $this->assertEquals(false, $date->isHoliday(), $date->getDateFormatted());

        $date->modify("+1 day");
        $this->assertEquals(true, $date->isHoliday(), $date->getDateFormatted());
    }

    public function testIsSunday()
    {
        $date = $this->createWorkdayIn2017("2017-04-16");
        $this->assertEquals(true, $date->isSunday(), $date->getDateFormatted());

        $date->modify("+1 day");
        $this->assertEquals(false, $date->isSunday(), $date->getDateFormatted());
    }

    public function testIsSundayOrHoliday()
    {
        // Sunday and holiday
        $date = $this->createWorkdayIn2017("2017-04-16");
        $this->assertEquals(true, $date->isSundayOrHoliday(), $date->getDateFormatted());

        // Holiday
        $date->modify("+1 day");
        $this->assertEquals(true, $date->isSundayOrHoliday(), $date->getDateFormatted());

        // None
        $date->modify("+1 day");
        $this->assertEquals(false, $date->isSundayOrHoliday(), $date->getDateFormatted());

        // Sunday
        $date->modify("+5 day");
        $this->assertEquals(true, $date->isSundayOrHoliday(), $date->getDateFormatted());
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
        $this->assertEquals($date->getDateFormatted(), "2017-05-02");

        $date->addWorkdays(2);
        $this->assertEquals($date->getDateFormatted(), "2017-05-04");

        $date->addWorkdays(5);
        $this->assertEquals($date->getDateFormatted(), "2017-05-11");

        $date->addWorkdays(-5);
        $this->assertEquals($date->getDateFormatted(), "2017-05-04");
    }

    public function testAddWorkdaysIfTodayIsHoliday()
    {
        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->addWorkdays(1);
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->addWorkdays(-1);
        $this->assertEquals($date->getDateFormatted(), "2017-04-13");
    }

    public function testNextLastWorkday()
    {
        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->nextWorkday();
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('next workday');
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->lastWorkday();
        $this->assertEquals($date->getDateFormatted(), "2017-04-13");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('last workday');
        $this->assertEquals($date->getDateFormatted(), "2017-04-13");

        $date = $this->createWorkdayIn2017("2017-04-18");
        $date->modify('last workday');
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");
        $date->modify('next workday');
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-14");
        $date->modify('last workday - 1 workdays');
        $this->assertEquals($date->getDateFormatted(), "2017-04-12");
    }

    public function testAddWorkdaysIfTodayIsWeekend()
    {
        $date = $this->createWorkdayIn2017("2017-04-15");
        $date->addWorkdays(1);
        $this->assertEquals($date->getDateFormatted(), "2017-04-18");

        $date = $this->createWorkdayIn2017("2017-04-15");
        $date->addWorkdays(-1);
        $this->assertEquals($date->getDateFormatted(), "2017-04-13");
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

        $date->modify('last day of -1 month');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-04-30 11:00:00");

        $date->modify('+1 hour')->modify('-1 hour');
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2017-04-30 11:00:00");
    }

    public function testClone()
    {
        $date1 = $this->createWorkdayIn2017("2017-05-24 10:00:00");
        $date2 = clone $date1;
        $date2->modify('+1 hour');

        $this->assertEquals($date1->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");
        $this->assertEquals($date2->format('Y-m-d H:i:s'), "2017-05-24 11:00:00");

        $date1 = $this->createWorkdayIn2017("2017-05-24 10:00:00");
        $date2 = $date1->clone();
        $date2->modify('+1 hour');

        $this->assertEquals($date1->format('Y-m-d H:i:s'), "2017-05-24 10:00:00");
        $this->assertEquals($date2->format('Y-m-d H:i:s'), "2017-05-24 11:00:00");
    }

    public function testFromTimestamp()
    {
        // Check if timestamp auto-defect works
        $date = new Workday(1577836800);
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2020-01-01 00:00:00");
        $this->assertEquals($date->getTimezone()->getName(), 'UTC');

        // Check if timestamp auto-defect works and timezone is ignored
        $date = new Workday('1577836800', new DateTimeZone('Europe/Paris'));
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2020-01-01 00:00:00");
        $this->assertEquals($date->getTimezone()->getName(), 'UTC');

        // Check if normal timestamp mode works and timezone is ignored
        $date = Workday::create('@1577836800', new DateTimeZone('Europe/Paris'));
        $this->assertEquals($date->format('Y-m-d H:i:s'), "2020-01-01 00:00:00");
        $this->assertEquals($date->getTimezone()->getName(), 'UTC');
    }

    public function testDiffWorkdaysBridgeDay()
    {
        $date1 = $this->createWorkdayIn2017("2017-05-24"); // wednesday
        $date2 = $this->createWorkdayIn2017("2017-05-25"); // thursday & holiday
        $date3 = $this->createWorkdayIn2017("2017-05-26"); // friday
        $date4 = $this->createWorkdayIn2017("2017-05-27"); // saturday & weekend

        $this->assertEquals(true, $date1->isWorkday());
        $this->assertEquals(false, $date2->isWorkday());
        $this->assertEquals(true, $date3->isWorkday());
        $this->assertEquals(false, $date4->isWorkday());

        $this->assertEquals(1, $date1->diffWorkdaysUntil($date2));
        $this->assertEquals(1, $date1->diffWorkdaysUntil($date3));
        $this->assertEquals(2, $date1->diffWorkdaysUntil($date4));

        $this->assertEquals(1, $date2->diffWorkdaysUntil($date3));
        $this->assertEquals(2, $date2->diffWorkdaysUntil($date4));

        $this->assertEquals(1, $date3->diffWorkdaysUntil($date4));
    }

    public function testDiffWorkdaysWeekend()
    {
        $date1 = $this->createWorkdayIn2017("2017-04-28"); // friday
        $date2 = $this->createWorkdayIn2017("2017-04-29"); // saturday & weekend
        $date3 = $this->createWorkdayIn2017("2017-05-01"); // monday & holiday
        $date4 = $this->createWorkdayIn2017("2017-05-02"); // tuesday
        $date5 = $this->createWorkdayIn2017("2017-05-03"); // wednesday

        $this->assertEquals(true, $date1->isWorkday());
        $this->assertEquals(false, $date2->isWorkday());
        $this->assertEquals(false, $date3->isWorkday());
        $this->assertEquals(true, $date4->isWorkday());
        $this->assertEquals(true, $date5->isWorkday());

        $this->assertEquals(0, $date1->diffWorkdaysUntil($date1));
        $this->assertEquals(0, $date2->diffWorkdaysUntil($date2));
        $this->assertEquals(0, $date2->diffWorkdaysUntil($date3));

        $date1->setTimezone(new DateTimeZone('Europe/Berlin'));
        $date2->setTimezone(new DateTimeZone('UTC'));
        $date4->setTimezone(new DateTimeZone('America/New_York'));

        $this->assertEquals(1, Workday::diffWorkdays($date1, $date2));
        $this->assertEquals(1, $date1->diffWorkdaysUntil($date2));
        $this->assertEquals(1, $date1->diffWorkdaysUntil($date3));
        $this->assertEquals(-1, $date1->diffWorkdaysSince($date2));

        $this->assertEquals(1, $date1->diffWorkdaysUntil($date4));
        $this->assertEquals(1, $date2->diffWorkdaysUntil($date4));
        $this->assertEquals(1, $date3->diffWorkdaysUntil($date4));

        $this->assertEquals(2, $date1->diffWorkdaysUntil($date5));
        $this->assertEquals(2, $date2->diffWorkdaysUntil($date5));
        $this->assertEquals(2, $date3->diffWorkdaysUntil($date5));
        $this->assertEquals(1, $date4->diffWorkdaysUntil($date5));
    }

    /**
     * @param string $date
     *
     * @return Workday
     * @throws Exception
     */
    public function createWorkdayIn2017($date)
    {
        $workday = new Workday($date);

        $workday->setHolidays([
            "2017-01-01" => "Neujahrstag",
            "2017-04-14" => "Karfreitag",
            "2017-04-17" => "Ostermontag",
            "2017-05-01" => "Tag der Arbeit", // monday
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
