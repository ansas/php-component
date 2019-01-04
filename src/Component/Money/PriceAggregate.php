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

use InvalidArgumentException;
use Traversable;

/**
 * Class PriceAggregate
 *
 * @package Ansas\Component\Money
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PriceAggregate extends PriceBase
{
    /**
     *
     */
    const ADD      = '+';
    const SUBTRACT = '-';

    /**
     * @var bool Keep empty tax rates (or not)
     */
    protected $keepEmptyTaxRates = false;

    /**
     * @var array List of prices per tax rate
     */
    protected $perTaxRate = [];

    /**
     * Constructor.
     *
     * @param Price[] $prices [optional]
     */
    public function __construct($prices = [])
    {
        $this->addPrices($prices);
    }

    /**
     * Return String representation of object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Create new instance.
     *
     * @param Price[] $prices [optional]
     *
     * @return static
     */
    public static function create($prices = [])
    {
        return new static($prices);
    }

    /**
     * Create new instance.
     *
     * @param array $value
     * @param bool  $validate [optional]
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createFromArray($value, $validate = true)
    {
        if (!isset($value['perTaxRate'])) {
            throw new InvalidArgumentException("key perTaxRate is missing on root level");
        }

        $priceAggregate = new static();

        foreach ($value['perTaxRate'] as $perTaxRate) {
            $priceAggregate->addPrice(Price::createFromArray($perTaxRate, Price::GROSS, $validate));
        }

        return $priceAggregate;
    }

    /**
     * Create new instance.
     *
     * @param string $value
     *
     * @return static
     */
    public static function createFromJson(string $value)
    {
        $value = json_decode($value, true);

        return static::createFromArray($value);
    }

    /**
     * Create new instance.
     *
     * @param Price $price
     *
     * @return static
     */
    public static function createFromPrice(Price $price)
    {
        return new static([$price]);
    }

    /**
     * Add price.
     *
     * @param Price  $price
     * @param string $operation [optional]
     *
     * @return $this
     */
    public function addPrice(Price $price, $operation = self::ADD)
    {
        $percent = $this->sanitizePercent($price);

        // Prepare new tax
        if (!$this->hasTaxPercent($percent)) {
            $this->perTaxRate[$percent] = [
                'gross'   => 0,
                'net'     => 0,
                'percent' => $percent,
            ];
            ksort($this->perTaxRate, SORT_NUMERIC);
        }

        // Switch positive <> negative prices if we want to subtract price
        if ($operation == self::SUBTRACT) {
            // Make sure original object is NOT changed
            $price = clone $price;
            $price->changeSign();
        }

        // Add price
        $this->perTaxRate[$percent]['gross'] += $price->get('gross');
        $this->perTaxRate[$percent]['net']   += $price->get('net');

        // Remove tax block if price is now 0.00
        if (
            !$this->getKeepEmptyTaxRates()
            && !Price::round($this->perTaxRate[$percent]['gross'])
            && !Price::round($this->perTaxRate[$percent]['net'])
        ) {
            unset($this->perTaxRate[$percent]);
        }

        return $this;
    }

    /**
     * Add price.
     *
     * @param PriceAggregate $priceAggregate
     * @param string         $operation [optional]
     *
     * @return $this
     */
    public function addPriceAggregate(PriceAggregate $priceAggregate, $operation = self::ADD)
    {
        return $this->addPrices($priceAggregate->getPerTaxRate(), $operation);
    }

    /**
     * Add price.
     *
     * @param Price[] $prices
     * @param string  $operation [optional]
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addPrices($prices, $operation = self::ADD)
    {
        if (!is_array($prices) && !$prices instanceof Traversable) {
            throw new InvalidArgumentException("prices must be iterable");
        }

        foreach ($prices as $price) {
            if (!$price instanceof Price) {
                throw new InvalidArgumentException("elements of prices must be instance of Price");
            }
            $this->addPrice($price, $operation);
        }

        return $this;
    }

    /**
     * Adjust gross and net value (on lowest tax rate) to new value if within tolerance.
     *
     * @param string $property
     * @param float  $new
     * @param float  $tolerance [optional]
     *
     * @return $this
     */
    public function adjustRoundingError($property, $new, $tolerance = 0.01)
    {
        $this->validateProperty($property);

        $old  = $this->get($property);
        $diff = Price::round($new - $old);

        if (abs($diff) <= abs($tolerance)) {
            $price = Price
                ::create()
                ->setGross($diff)
                ->setNet($diff)
                ->setTax(0)
                ->setTaxPercent(key($this->perTaxRate))
            ;

            $this->addPrice($price);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $result = [
            'gross'      => $this->getGross(),
            'net'        => $this->getNet(),
            'perTaxRate' => [],
        ];

        foreach ($this->getPerTaxRate() as $percent => $perTaxRate) {
            $result['perTaxRate'][$percent] = [
                'gross'   => $perTaxRate->getGross(),
                'net'     => $perTaxRate->getNet(),
                'percent' => $perTaxRate->getTaxPercent(),
            ];
        }

        return $result;
    }

    /**
     * @param float $factor
     *
     * @return $this
     */
    public function changeToFactor($factor)
    {
        if (!$factor) {
            $this->perTaxRate = [];

            return $this;
        }

        // Note: do not round here!
        foreach (array_keys($this->perTaxRate) as $percent) {
            foreach (['gross', 'net'] as $property) {
                $this->perTaxRate[$percent][$property] *= $factor;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function countTaxRates()
    {
        return count($this->perTaxRate);
    }

    /**
     * @inheritdoc
     */
    public function get($property)
    {
        $result = null;

        switch ($property) {
            case 'keepEmptyTaxRates':
                $result = $this->keepEmptyTaxRates;
                break;

            case 'perTaxRate':
                $result = $this->getPerTaxRate();
                break;

            case 'taxRates':
                $result = $this->getTaxRates();
                break;

            default:
                $result = 0.00;
                foreach ($this->getPerTaxRate() as $perTaxRate) {
                    $result += $perTaxRate->get($property);
                }
                $result = Price::round($result);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function getKeepEmptyTaxRates()
    {
        return $this->get('keepEmptyTaxRates');
    }

    /**
     * @return Price[]
     */
    public function getPerTaxRate()
    {
        $result = [];

        foreach ($this->perTaxRate as $percent => $perTaxRate) {
            $result[$percent] = Price::createFromArray($perTaxRate, Price::GROSS, false);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTaxRates()
    {
        return array_keys($this->perTaxRate);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function hasTaxPercent($value)
    {
        $percent = $this->sanitizePercent($value);

        return isset($this->perTaxRate[$percent]);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->countTaxRates() == 0;
    }

    /**
     * @return $this
     */
    public function round()
    {
        foreach (array_keys($this->perTaxRate) as $percent) {
            foreach (['gross', 'net'] as $property) {
                $this->perTaxRate[$percent][$property] = Price::round($this->perTaxRate[$percent][$property]);
            }
        }

        return $this;
    }

    /**
     * @param string $property
     * @param float  $value
     *
     * @return $this
     */
    public function set(string $property, $value)
    {
        $this->validateProperty($property);

        // Note: do not round here!
        $old = 0;
        foreach (array_keys($this->perTaxRate) as $percent) {
            $old += $this->perTaxRate[$percent][$property];
        }

        if ($old) {
            $this->changeToFactor($value / $old);
        }

        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setGross(float $value)
    {
        return $this->set('gross', $value);
    }

    /**
     * @param bool $round
     *
     * @return $this
     */
    public function setKeepEmptyTaxRates($keepEmptyTaxRates)
    {
        $this->keepEmptyTaxRates = (bool) $keepEmptyTaxRates;

        return $this;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setNet(float $value)
    {
        return $this->set('net', $value);
    }

    /**
     * Subtract price.
     *
     * @param Price $price
     *
     * @return $this
     */
    public function subtractPrice(Price $price)
    {
        return $this->addPrice($price, self::SUBTRACT);
    }

    /**
     * Subtract price.
     *
     * @param PriceAggregate $priceAggregate
     *
     * @return $this
     */
    public function subtractPriceAggregate(PriceAggregate $priceAggregate)
    {
        return $this->addPriceAggregate($priceAggregate, self::SUBTRACT);
    }

    /**
     * Subtract price.
     *
     * @param Price[] $prices
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function subtractPrices($prices)
    {
        return $this->addPrices($prices, self::SUBTRACT);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function sanitizePercent($value)
    {
        if ($value instanceof Price) {
            return (string) $value->getTaxPercent();
        }

        return (string) Price::round($value);
    }

    /**
     * @param string $property
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function validateProperty(string $property)
    {
        if (!in_array($property, ['gross', 'net'])) {
            throw new InvalidArgumentException("Property {$property} does not exist");
        }

        return $this;
    }
}
