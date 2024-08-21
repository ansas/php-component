<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    public function testGetDigits()
    {
        $this->assertEquals('', Number::getDigits(null));
        $this->assertEquals('', Number::getDigits(''));
        $this->assertEquals('', Number::getDigits(0));
        $this->assertEquals('', Number::getDigits(50));
        $this->assertEquals('', Number::getDigits('50.'));
        $this->assertEquals('', Number::getDigits(0.0));
        $this->assertEquals('', Number::getDigits(1.0));
        $this->assertEquals('1', Number::getDigits(1.1));
        $this->assertEquals('01', Number::getDigits(1.01));
        $this->assertEquals('123', Number::getDigits(1.123));
    }

    public function testCountDigits()
    {
        $this->assertEquals(0, Number::countDigits(null));
        $this->assertEquals(0, Number::countDigits(''));
        $this->assertEquals(0, Number::countDigits(0));
        $this->assertEquals(0, Number::countDigits(50));
        $this->assertEquals(0, Number::countDigits('50.'));
        $this->assertEquals(0, Number::countDigits(0.0));
        $this->assertEquals(0, Number::countDigits(1.0));
        $this->assertEquals(1, Number::countDigits(1.1));
        $this->assertEquals(2, Number::countDigits(1.01));
        $this->assertEquals(3, Number::countDigits(1.123));
    }

    public function testToNearestStepDefaultMode()
    {
        $this->assertEquals(0, Number::toNearestStep(null, null));
        $this->assertEquals(5, Number::toNearestStep(5, 0));

        $this->assertEquals(0, Number::toNearestStep(null, 5));
        $this->assertEquals(0, Number::toNearestStep('', 5));
        $this->assertEquals(0, Number::toNearestStep(0, 5));
        $this->assertEquals(0, Number::toNearestStep(2.4, 5));
        $this->assertEquals(5, Number::toNearestStep(2.6, 5));
        $this->assertEquals(5, Number::toNearestStep(5, 5));
        $this->assertEquals(5, Number::toNearestStep('5', 5));
        $this->assertEquals(10, Number::toNearestStep(7.6, 5));
        $this->assertEquals(110, Number::toNearestStep(111, 5));

        $this->assertEquals(0, Number::toNearestStep(null, 0.25));
        $this->assertEquals(0, Number::toNearestStep('', 0.25));
        $this->assertEquals(0, Number::toNearestStep(0, 0.25));
        $this->assertEquals(0, Number::toNearestStep(0.12, 0.25));
        $this->assertEquals(0.25, Number::toNearestStep(0.13, 0.25));
        $this->assertEquals(0.25, Number::toNearestStep(0.25, 0.25));
        $this->assertEquals(0.25, Number::toNearestStep(0.26, 0.25));
        $this->assertEquals(2.50, Number::toNearestStep(2.4, 0.25));
        $this->assertEquals(2.50, Number::toNearestStep(2.6, 0.25));
        $this->assertEquals(2.75, Number::toNearestStep(2.71111, 0.25));
        $this->assertEquals(2.7, Number::toNearestStep(2.8, 0.3));
        $this->assertEquals(2.7, Number::toNearestStep(2.8, 0.3));
        $this->assertEquals(2.875, Number::toNearestStep(2.9, 0.125));
        $this->assertEquals(5.00, Number::toNearestStep(5, 0.25));
        $this->assertEquals(5.00, Number::toNearestStep('5', 0.25));
        $this->assertEquals(7.50, Number::toNearestStep(7.6, 0.25));
        $this->assertEquals(7.5, Number::toNearestStep(7.6, 2.5));
    }

    public function testToNearestStepExplicitMode()
    {
        $this->assertEquals(17.9, Number::toNearestStep(17.9, 0.05, Number::NEAREST_DOWN));

        $this->assertEquals(7.5, Number::toNearestStep(7.6, 2.5, Number::NEAREST_ROUND));
        $this->assertEquals(7.5, Number::toNearestStep(5.1, 2.5, Number::NEAREST_UP));
        $this->assertEquals(7.5, Number::toNearestStep(9.9, 2.5, Number::NEAREST_DOWN));

        $this->assertEquals(7.5, Number::toNearestStep(7.5, 2.5, Number::NEAREST_ROUND));
        $this->assertEquals(7.5, Number::toNearestStep(7.5, 2.5, Number::NEAREST_UP));
        $this->assertEquals(7.5, Number::toNearestStep(7.5, 2.5, Number::NEAREST_DOWN));
    }

    public function testToNearestStepUnknownMode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Mode 'wrongValue' not supported");
        $this->assertEquals(7.5, Number::toNearestStep(9.9, 2.5, 'wrongValue'));
    }

    public function testToPositive()
    {
        $this->assertEquals(0, Number::toPositive(0));
        $this->assertEquals(1, Number::toPositive(1));
        $this->assertEquals(1.1, Number::toPositive(1.1));
        $this->assertEquals(0, Number::toPositive(-0));
        $this->assertEquals(1, Number::toPositive(-1));
        $this->assertEquals(1.1, Number::toPositive(-1.1));
    }

    public function testToNegative()
    {
        $this->assertEquals(0, Number::toNegative(0));
        $this->assertEquals(-1, Number::toNegative(1));
        $this->assertEquals(-1.1, Number::toNegative(1.1));
        $this->assertEquals(0, Number::toNegative(-0.00));
        $this->assertEquals(-1, Number::toNegative(-1));
        $this->assertEquals(-1.1, Number::toNegative(-1.1));
    }

    public function testToReadableSize()
    {
        Format::setLocale('de_DE');
        $this->assertEquals('1,44 MB', Number::toReadableSize(1_440_000, 2, 'metric'));
        $this->assertEquals('1.4 MB', Number::toReadableSize(1_440_000, 1, 'metric', 'en'));
    }

    public function testToReadableTime()
    {
        Format::setLocale('de_DE');
        $this->assertEquals('0,01 sec', Number::toReadableTime(0.01101, 2));
        $this->assertEquals('0.01 sec', Number::toReadableTime(0.01101, 2, 'en_GB'));
    }

    public function testToReadableWeight()
    {
        Format::setLocale('de_DE');
        $this->assertEquals(null, Number::toReadableWeight(null, 2));
        $this->assertEquals('1 g', Number::toReadableWeight(1));
        $this->assertEquals('999 g', Number::toReadableWeight(999, 1));
        $this->assertEquals('1,00 kg', Number::toReadableWeight(1_001, 2));
        $this->assertEquals('1,5 kg', Number::toReadableWeight(1_456));
        $this->assertEquals('1.46 kg', Number::toReadableWeight(1_456, 2, 'en_US'));
    }
}
