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
 * Class CsvReaderTest
 *
 * @package Ansas\Component\Csv
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

    public function testWeekend()
    {
        $date = new Workday();
        for ($i = 0; $i < 7; $i++) {
            $isWeekend = in_array($date->format($date::DAY_WEEKDAY_FORMAT), $date->getWeekendDays());
            $this->assertEquals($isWeekend, $date->isWeekend());
        }
    }

    public function testHoliday()
    {
        $date = new Workday("2017-04-30");
        $this->assertEquals(false, $date->isHoliday(), $date->format($date::DAY_DATE_FORMAT));

        $date->modify("+1 day");
        $this->assertEquals(true, $date->isHoliday(), $date->format($date::DAY_DATE_FORMAT));
    }

    public function testAddWorkdays()
    {
        $date = new Workday("2017-04-28");
        $date->addWorkdays(1);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-02");

        $date->addWorkdays(2);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-04");

        $date->addWorkdays(5);
        $this->assertEquals($date->format($date::DAY_DATE_FORMAT), "2017-05-11");
    }
}
