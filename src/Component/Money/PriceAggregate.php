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
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createFromArray($value)
    {
        if (!isset($value['perTaxRate'])) {
            throw new InvalidArgumentException("key perTaxRate is missing on root level");
        }

        $priceAggregate = new static();

        foreach ($value['perTaxRate'] as $perTaxRate) {
            $priceAggregate->addPrice(Price::createFromArray($perTaxRate));
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
        $this->perTaxRate[$percent]['gross'] = Price::round($this->perTaxRate[$percent]['gross'] + $price->get('gross'));
        $this->perTaxRate[$percent]['net']   = Price::round($this->perTaxRate[$percent]['net'] + $price->get('net'));

        // Remove tax block if price is now 0.00
        if (0.00 == $this->perTaxRate[$percent]['gross'] && 0.00 == $this->perTaxRate[$percent]['net']) {
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
     * @return Price[]
     */
    public function getPerTaxRate()
    {
        $result = [];

        foreach ($this->perTaxRate as $percent => $perTaxRate) {
            $result[$percent] = Price::createFromArray($perTaxRate);
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
}
