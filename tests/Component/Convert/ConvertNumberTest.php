<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Convert;

use PHPUnit\Framework\TestCase;

class ConvertNumberTest extends TestCase
{
    public function testSanitize()
    {
        $this->assertEquals(1325125.54, ConvertNumber::asFloat('1.325.125,54'));
        $this->assertEquals(1325125.54, ConvertNumber::asFloat('1,325,125.54'));
        $this->assertEquals(-1325125.54, ConvertNumber::asFloat('-1.325.125,54'));
        $this->assertEquals(-1325125.54, ConvertNumber::asFloat('-1,325,125.54'));
        $this->assertEquals(59.95, ConvertNumber::asFloat('59,95'));
        $this->assertEquals(1, ConvertNumber::asFloat(true));
        $this->assertEquals(0, ConvertNumber::asFloat(false));
        $this->assertEquals(0, ConvertNumber::asFloat(0));
        $this->assertEquals(1, ConvertNumber::asFloat(1));
        $this->assertEquals(0, ConvertNumber::asFloat([]));
    }
}
