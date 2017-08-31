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

use PHPUnit\Framework\TestCase;

/**
 * Class PriceAggregate
 *
 * @package Ansas\Component\Money
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PriceAggregateTest extends TestCase
{
    public function testClassExists()
    {
        $priceAggregate = new PriceAggregate();
        $this->assertInstanceOf(PriceAggregate::class, $priceAggregate);
    }

    public function testEmpty()
    {
        $priceAggregate = new PriceAggregate();
        $this->assertEquals('{"gross":0,"net":0,"perTaxRate":[]}', $priceAggregate->toJson());
        $this->assertEquals([], $priceAggregate->getPerTaxRate());
        $this->assertEquals([], $priceAggregate->getTaxRates());
        $this->assertEquals(0, $priceAggregate->countTaxRates());
        $this->assertEquals(0, $priceAggregate->getTax());
        $this->assertEquals(0, $priceAggregate->getNet());
        $this->assertEquals(0, $priceAggregate->getGross());
    }

    public function testSinglePayment()
    {
        $priceAggregate = new PriceAggregate();
        $priceAggregate->addPrice(Price::createFromArray(['gross' => 119, 'net' => 100]));
        $this->assertEquals('{"gross":119,"net":100,"perTaxRate":{"19":{"gross":119,"net":100,"percent":19}}}',
            $priceAggregate->toJson());
        $this->assertEquals(19, $priceAggregate->getTax());
        $this->assertEquals([19], $priceAggregate->getTaxRates());
        $this->assertEquals(1, $priceAggregate->countTaxRates());
    }

    public function testMultiPayments()
    {
        $priceAggregate = new PriceAggregate();
        $priceAggregate->addPrice(Price::createFromArray(['gross' => 119, 'net' => 100]));
        $priceAggregate->addPrice(Price::createFromArray(['gross' => 107, 'net' => 100]));
        $this->assertEquals('{"gross":226,"net":200,"perTaxRate":{"7":{"gross":107,"net":100,"percent":7},"19":{"gross":119,"net":100,"percent":19}}}',
            $priceAggregate->toJson());
        $this->assertEquals(26, $priceAggregate->getTax());
        $this->assertEquals([7, 19], $priceAggregate->getTaxRates());
        $this->assertEquals(2, $priceAggregate->countTaxRates());
    }

    public function testCreateFromJson()
    {
        $json = '{"gross":226,"net":200,"perTaxRate":{"7":{"gross":107,"net":100,"percent":7},"19":{"gross":119,"net":100,"percent":19}}}';

        $priceAggregate = PriceAggregate::createFromJson($json);
        $this->assertEquals($json, $priceAggregate->toJson());
    }

    public function testAddPriceAggregate()
    {
        $price = Price::createFromArray(['gross' => 16.95, 'net' => 15.84, 'percent' => 7]);

        $priceAggregateIntermediate = new PriceAggregate();
        $priceAggregateIntermediate->addPrice($price);
        $this->assertEquals('{"gross":16.95,"net":15.84,"perTaxRate":{"7":{"gross":16.95,"net":15.84,"percent":7}}}',
            $priceAggregateIntermediate->toJson());

        $priceAggregate = new PriceAggregate();
        $priceAggregate->addPriceAggregate($priceAggregateIntermediate);

        $this->assertEquals('{"gross":16.95,"net":15.84,"perTaxRate":{"7":{"gross":16.95,"net":15.84,"percent":7}}}',
            $priceAggregate->toJson());
    }

    public function testChangeToFactor()
    {
        $inputJson = '{"gross":226,"net":200,"perTaxRate":{"7":{"gross":107,"net":100,"percent":7},"19":{"gross":119,"net":100,"percent":19}}}';

        $priceAggregate = PriceAggregate::createFromJson($inputJson);

        $priceAggregate->changeToFactor(2);
        $this->assertEquals('{"gross":452,"net":400,"perTaxRate":{"7":{"gross":214,"net":200,"percent":7},"19":{"gross":238,"net":200,"percent":19}}}', $priceAggregate->toJson());

        $priceAggregate->changeToFactor(0.5);
        $this->assertEquals($inputJson, $priceAggregate->toJson());

        $priceAggregate->changeToFactor(0);
        $this->assertEquals('{"gross":0,"net":0,"perTaxRate":[]}', $priceAggregate->toJson());
    }

    public function testSubtractPriceAggregate()
    {
        // Create price
        $price = Price::createFromArray(['gross' => 16.95, 'net' => 15.84, 'percent' => 7]);
        $this->assertEquals('{"gross":16.95,"net":15.84,"tax":1.11,"taxPercent":7,"taxRate":1.07}', $price->toJson());

        // Add price to aggretate object
        $priceAggregateIntermediate = new PriceAggregate();
        $priceAggregateIntermediate->addPrice($price);
        $this->assertEquals('{"gross":16.95,"net":15.84,"perTaxRate":{"7":{"gross":16.95,"net":15.84,"percent":7}}}',
            $priceAggregateIntermediate->toJson());

        // Subtract same price to aggretate object
        $priceAggregateIntermediate->subtractPrice($price);
        $this->assertEquals('{"gross":0,"net":0,"perTaxRate":[]}', $priceAggregateIntermediate->toJson());

        // Make sure original object is not changed
        $this->assertEquals('{"gross":16.95,"net":15.84,"tax":1.11,"taxPercent":7,"taxRate":1.07}', $price->toJson());
    }
}
