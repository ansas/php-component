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

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ConvertPriceTest extends TestCase
{
    public function testSanitize()
    {
        $this->assertEquals(0, ConvertPrice::getInstance()->sanitize(null));
        $this->assertEquals(0, ConvertPrice::getInstance()->sanitize(''));
        $this->assertEquals(0, ConvertPrice::getInstance()->sanitize('-1.123E-11'));
        $this->assertEquals(0, ConvertPrice::getInstance()->sanitize('0.000.000,00'));
        $this->assertEquals(-12345.67, ConvertPrice::getInstance()->sanitize('12.345,67-'));
        $this->assertEquals(12345.67, ConvertPrice::getInstance()->sanitize('12,345.67'));
        $this->assertEquals(12345.67, ConvertPrice::getInstance()->sanitize('the price is 12,345.67 EUR'));
    }
}
