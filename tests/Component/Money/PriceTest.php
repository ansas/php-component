<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Money;

use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Class PriceTest
 *
 * @package Ansas\Component\Money
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PriceTest extends TestCase
{
    protected static $json = '{"gross":119,"net":100,"tax":19,"taxPercent":19,"taxRate":1.19}';

    public function testClassExists()
    {
        $price = new Price();
        $this->assertInstanceOf(Price::class, $price);
    }

    public function testEmpty()
    {
        $this->expectException(LogicException::class);
        $price = new Price();
        $price->toJson();
    }

    public function testDefault()
    {
        $price = Price
            ::create()
            ->setGross(119)
            ->setNet(100)
        ;
        $this->assertEquals(119, $price->getPrice());
    }

    public function testCreate()
    {
        $price = new Price(119);
        $this->assertEquals(119, $price->getGross());
        $this->assertEquals(Price::GROSS, $price->getDefaultType());
        $this->assertEquals("119", $price);

        $price = Price::create(119, Price::NET);
        $this->assertEquals(119, $price->getNet());
        $this->assertEquals(Price::NET, $price->getDefaultType());
        $this->assertEquals("119", $price);

        $this->assertEquals(static::$json, Price::createFromArray(['gross' => -119, 'tax' => 19])->changeSign()->toJson());
        $this->assertEquals(static::$json, Price::createFromArray(['tax' => 19, 'percent' => 19])->toJson());
    }

    public function testChangeSign()
    {
        $this->assertEquals(-119, Price::create(119)->setNet(100)->changeSign()->getGross());
        $this->assertEquals(119, Price::create(-119)->setNet(-100)->changeSign()->getGross());
        $this->assertEquals(0, Price::create(0)->setNet(0)->changeSign()->getGross());
    }

    public function testChangeToFactor()
    {
        foreach ([1, -1, 0.5, 2] as $factor) {
            $price = Price
                ::create(119)
                ->setNet(100)
                ->changeToFactor($factor)
            ;

            $this->assertEquals(round(119 * $factor, $price->getRoundPrecision()), $price->getGross());
            $this->assertEquals(round(100 * $factor, $price->getRoundPrecision()), $price->getNet());
            $this->assertEquals(round(19 * $factor, $price->getRoundPrecision()), $price->getTax());
        }
    }

    public function testToString()
    {
        $price = new Price(119);
        $this->assertEquals("119", "$price");

        $price = Price::create(119, Price::NET);
        $this->assertEquals("119", "$price");
    }

    public function testTaxPercentException()
    {
        $this->expectException(LogicException::class);
        $price = new Price(119);
        $price->getTaxPercent();
    }

    public function testTaxRateException()
    {
        $this->expectException(LogicException::class);
        $price = new Price(119);
        $price->getTaxRate();
    }

    public function testTaxPercentAndRate()
    {
        $price = new Price(119);

        $price->setTaxPercent(19);

        $this->assertEquals(19, $price->getTaxPercent());
        $this->assertEquals(1.19, $price->getTaxRate());

        $price->setTaxRate(1.20);

        $this->assertEquals(20, $price->getTaxPercent());
        $this->assertEquals(1.20, $price->getTaxRate());
    }

    public function testCalculation()
    {
        $this->assertEquals(static::$json, json_encode(Price::create()->setGross(119)->setNet(100)));

        $this->assertEquals(static::$json, Price::create()->setGross(119)->setNet(100)->toJson());
        $this->assertEquals(static::$json, Price::create()->setGross(119)->setTax(19)->toJson());
        $this->assertEquals(static::$json, Price::create()->setNet(100)->setTax(19)->toJson());

        $this->assertEquals(static::$json, Price::create()->setTaxRate(1.19)->setGross(119)->toJson());
        $this->assertEquals(static::$json, Price::create()->setTaxRate(1.19)->setNet(100)->toJson());
        $this->assertEquals(static::$json, Price::create()->setTaxRate(1.19)->setTax(19)->toJson());
    }
}
