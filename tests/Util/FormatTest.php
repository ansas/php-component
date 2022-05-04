<?php

namespace Ansas\Util;

use DateTime;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        setlocale(LC_ALL, 'de_DE.utf8', 'de_DE');
        setlocale(LC_NUMERIC, 'C');
    }

    public function testCurreny()
    {
        Format::setLocale('en_US');
        $this->assertEquals('$1.55', Format::currency("1.55"));
        $this->assertEquals('$1,555,555,555.00', Format::currency("1555555555"));

        Format::setLocale('de_DE');
        $this->assertEquals('1,55 €', Format::currency("1.55"));
        $this->assertEquals('1.555.555.555,00 €', Format::currency("1555555555"));

        Format::setCurrencySymbol('EUR');
        $this->assertEquals('1,55 EUR', Format::currency("1.55"));
        $this->assertEquals('1.555.555.555,00 EUR', Format::currency("1555555555"));
        $this->assertEquals('1,55 USD', Format::currency("1.55", ['currencySymbol' => 'USD']));

        Format::setCurrencySymbol(null);
        Format::setLocale('en_US');
        $this->assertEquals('$1.55', Format::currency("1.55"));

        Format::setCurrencySymbol('USD');
        $this->assertEquals('USD 1.55', Format::currency("1.55"));
        $this->assertEquals('1,55 USD', Format::currency("1.55", ['locale' => 'de_DE']));

        $this->expectExceptionMessage('type float');
        Format::currency('20?$%HFÄ20');
    }

    public function testLocaleException()
    {
        $this->expectExceptionMessage('locale "not_AVAILABLE" not available');
        Format::setLocale('not_AVAILABLE');
    }

    public function testDate()
    {
        $datetime = new DateTime();
        $datetime->setTimestamp(1643124492);

        Format::setLocale('de_DE');
        $this->assertEquals('25.01.2022', Format::date($datetime));
        $this->assertEquals('25.01.2022', Format::date('22-01-25'));
        $this->assertEquals('Jan 25, 2022', Format::date('22-01-25', ['locale' => 'en_US']));

        Format::setDateFormat('short');
        $this->assertEquals('25.01.22', Format::date($datetime));

        Format::setDateFormat('medium');
        $this->assertEquals('25.01.2022', Format::date($datetime));

        Format::setDateFormat('long');
        $this->assertEquals('25. Januar 2022', Format::date($datetime));

        Format::setDateFormat('full');
        $this->assertEquals('Dienstag, 25. Januar 2022', Format::date($datetime));
        $this->assertEquals('25/01/2022', Format::date($datetime, ['locale' => 'fr_FR', 'dateFormat' => 'short']));

        Format::setCalendarFormat('traditional');
        Format::setDateFormat('full');
        $this->assertEquals('Samstag, 1. Januar 2022', Format::date('2022-01-01'));

        $this->expectExceptionMessage('Failed to parse time string');
        Format::date('20?$%HFÄ20');
    }

    public function testTime()
    {
        $datetime = new DateTime();
        $datetime->setTimestamp(1643124492);

        Format::setLocale('de_DE');
        Format::setTimezone('Europe/Berlin');
        $this->assertEquals('00:00:00', Format::time('22-01-25'));

        $this->assertEquals('16:28:12', Format::time($datetime));
        Format::setTimezone('America/New_York');
        $this->assertEquals('10:28:12', Format::time($datetime));

        $this->assertEquals('12:00:00 AM', Format::time('22-01-25', ['locale' => 'en_US']));

        Format::setTimeFormat('short');
        $this->assertEquals('10:28', Format::time($datetime));

        Format::setTimeFormat('medium');
        $this->assertEquals('10:28:12', Format::time($datetime));

        Format::setTimeFormat('long');
        $this->assertEquals('10:28:12 GMT-5', Format::time($datetime));

        Format::setTimeFormat('full');
        $this->assertEquals('10:28:12 Nordamerikanische Ostküsten-Normalzeit', Format::time($datetime));

        Format::setLocale('en_US');
        $this->assertEquals('10:28:12 AM Eastern Standard Time', Format::time($datetime));
        $this->assertEquals('10:28 AM', Format::time($datetime, ['timeFormat' => 'short']));

        Format::setCalendarFormat('traditional');
        Format::setDateFormat('full');
        $this->assertEquals('12:00:00 AM Eastern Standard Time', Format::time('2022-01-01'));
        $this->assertEquals('4:28:12 PM Central European Standard Time', Format::time($datetime, ['timezone' => 'Europe/Berlin']));
        $this->assertEquals('7:28:12 AM Pacific Standard Time', Format::time($datetime, ['timezone' => 'America/Los_Angeles']));

        $this->expectExceptionMessage('Failed to parse time string');
        Format::time('20?$%HFÄ20');
    }

    public function testText()
    {
        $this->assertEquals('aaaaaaaaaa', Format::text('aaaaaaaaaa'));
        Format::setTextLimit(5);
        $this->assertEquals('aa...', Format::text('aaaaaaaaaa'));
        Format::setTextLimit(10);
        $this->assertEquals('aaaaa...', Format::text('aaaaa aaaaa'));

        $this->expectExceptionMessage('type string');
        Format::text(null);
    }

    public function testNumber()
    {
        Format::setLocale('de_DE');
        $this->assertEquals("15", Format::number(15));
        $this->assertEquals("1.500", Format::number(1500));
        $this->assertEquals("15,00", Format::number(15, ['fractionDigits' => 2]));
        $this->assertEquals("1.500,00", Format::number(1500, ['fractionDigits' => 2]));

        Format::setLocale('en_US');
        $this->assertEquals("15", Format::number('15'));
        $this->assertEquals("1,500", Format::number('1500'));
        $this->assertEquals("15.00", Format::number(15, ['fractionDigits' => 2]));
        $this->assertEquals("1,500.00", Format::number(1500, ['fractionDigits' => 2]));

        if (((float) phpversion()) >= 8.0) {
            $this->expectExceptionMessage('type int|float');
            Format::number('20?$%HFÄ20');
        }
    }

    public function testFormat()
    {
        Format::setLocale('en_US');
        $this->assertEquals('1,500', Format::format(1500, 'number'));
        $this->assertEquals('1,500.00', Format::format(1500, 'number', ['fractionDigits' => 2]));

        $this->assertEquals('aaaaaaaaaa', Format::format('aaaaaaaaaa', 'text'));
        Format::setTextLimit(5);
        $this->assertEquals('aa...', Format::format('aaaaaaaaaa', 'text'));
        $this->assertEquals('aaaaaa...', Format::format('aaaaaaaaaa', 'text', ['textLimit' => 9]));

        $datetime = new DateTime();
        $datetime->setTimestamp(1643124492);
        Format::setTimezone('America/New_York');
        $this->assertEquals('10:28 AM', Format::format($datetime, 'time',  ['timeFormat' => 'short']));

        Format::setDateFormat('full');
        $this->assertEquals('Dienstag, 25. Januar 2022', Format::format($datetime, 'date', ['locale' => 'de_DE']));

        Format::setCurrencySymbol('EUR');
        $this->assertEquals('EUR 1.55', Format::format('1.55', 'currency'));
        $this->assertEquals('1,55 EUR', Format::format('1.55', 'currency', ['locale' => 'de_DE']));

        $this->expectExceptionMessage('unknown type XXX');
        Format::format('aaaaaaaaaa', 'XXX');
    }
}
