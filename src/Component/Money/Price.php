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
use LogicException;
use Traversable;

/**
 * Class Price
 *
 * Set price and calculate rest. Also helps prevent float errors by rounding everything.
 *
 * @package Ansas\Component\Money
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Price extends PriceBase
{
    /**
     * Price default constants
     */
    const GROSS = 'gross';
    const NET   = 'net';

    /**
     * @var int Rounding precision
     */
    protected static $roundPrecision = 2;

    /**
     * @var array Translation table (adds property aliases)
     */
    protected static $translate = [
        'percent' => 'taxPercent',
        'rate'    => 'taxRate',
    ];

    /**
     * @var string Default price type (gross or net) for __toString and getPrice()
     */
    protected $defaultType;

    /**
     * @var string Price (gross)
     */
    protected $gross;

    /**
     * @var float Price net
     */
    protected $net;

    /**
     * @var float Tax
     */
    protected $tax;

    /**
     * @var float Tax percent (e. g. 19)
     */
    protected $taxPercent;

    /**
     * @var float Tax rate (e. g. 1.19)
     */
    protected $taxRate;

    /**
     * Constructor.
     *
     * @param float  $price     [optional]
     * @param string $priceType [optional]
     */
    public function __construct($price = null, string $priceType = self::GROSS)
    {
        if (null !== $price) {
            $this->set($priceType, $price, false);
        }

        $this->setDefaultType($priceType);
    }

    /**
     * Return String representation of object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getPrice();
    }

    /**
     * Create new instance via static method.
     *
     * @param float  $price     [optional]
     * @param string $priceType [optional]
     *
     * @return static
     */
    public static function create($price = null, string $priceType = self::GROSS)
    {
        return new static($price, $priceType);
    }

    /**
     * Create new instance via static method.
     *
     * @param array  $properties
     * @param string $priceType [optional]
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createFromArray($properties, string $priceType = self::GROSS)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new InvalidArgumentException("prices must be iterable");
        }

        $price = new static(null, $priceType);

        foreach ($properties as $property => $value) {
            $price->set($property, $value, false);
        }

        if (count($properties) > 1) {
            $price->calculate();
        }

        return $price;
    }

    /**
     * @return int
     */
    public static function getRoundPrecision()
    {
        return self::$roundPrecision;
    }

    /**
     * @param mixed $value
     * @param int   $precision [optional] defaults to static::getRoundPrecision()
     *
     * @return float
     * @throws InvalidArgumentException
     */
    public static function round($value, int $precision = null)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Value must be numeric");
        }

        return round($value, $precision ?? static::getRoundPrecision());
    }

    /**
     * @param int $roundPrecision
     */
    public static function setRoundPrecision(int $roundPrecision)
    {
        static::$roundPrecision = $roundPrecision;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'gross'      => $this->get('gross'),
            'net'        => $this->get('net'),
            'tax'        => $this->get('tax'),
            'taxPercent' => $this->get('taxPercent'),
            'taxRate'    => $this->get('taxRate'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function get($property)
    {
        $property = $this->translateProperty($property);

        $this->validateProperty($property, true);

        return $this->{$property};
    }

    /**
     * @param bool $calculateMissing [optional]
     *
     * @return $this
     */
    public function calculate($calculateMissing = true)
    {
        if ($calculateMissing) {
            $this->calculateGross();
            $this->calculateNet();
            $this->calculateTax();
            $this->calculateTaxRate();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function changeSign()
    {
        $properties = [
            'gross',
            'net',
            'tax',
        ];

        $this->needed($properties);

        foreach ($properties as $property) {
            $this->set($property, $this->get($property) * -1);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultType()
    {
        return $this->get('defaultType');
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->get($this->getDefaultType());
    }

    /**
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->get('taxPercent');
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->get('taxRate');
    }

    /**
     * @param string $property
     * @param mixed  $value
     * @param bool   $calculateMissing [optional]
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function set(string $property, $value, $calculateMissing = true)
    {
        $property = $this->translateProperty($property);

        $this->validateProperty($property);

        switch ($property) {
            case 'defaultType':
                $value = strtolower($value);

                if (!in_array($value, [static::GROSS, static::NET])) {
                    throw new InvalidArgumentException("taxPercent invalid");
                }

                break;

            case 'taxPercent':
                if ($value < 0 || $value >= 100) {
                    throw new InvalidArgumentException("taxPercent invalid");
                }

                $this->{'taxRate'} = static::round($value / 100 + 1);
                break;

            case 'taxRate':
                if ($value < 1 || $value >= 2) {
                    throw new InvalidArgumentException("taxRate invalid");
                }

                $this->{'taxPercent'} = static::round($value * 100 - 100);
                break;
        }

        if (is_numeric($value)) {
            $this->{$property} = static::round($value);
            $this->calculate($calculateMissing);
        } else {
            $this->{$property} = $value;
        }

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDefaultType(string $value)
    {
        return $this->set('defaultType', $value, false);
    }

    /**
     * @param float $value
     * @param bool  $calculateMissing [optional]
     *
     * @return $this
     */
    public function setGross(float $value, $calculateMissing = true)
    {
        return $this->set('gross', $value, $calculateMissing);
    }

    /**
     * @param float $value
     * @param bool  $calculateMissing [optional]
     *
     * @return $this
     */
    public function setNet(float $value, $calculateMissing = true)
    {
        return $this->set('net', $value, $calculateMissing);
    }

    /**
     * @param float $value
     * @param bool  $calculateMissing [optional]
     *
     * @return $this
     */
    public function setTax(float $value, $calculateMissing = true)
    {
        return $this->set('tax', $value, $calculateMissing);
    }

    /**
     * @param float $value
     * @param bool  $calculateMissing [optional]
     *
     * @return $this
     */
    public function setTaxPercent(float $value, $calculateMissing = true)
    {
        return $this->set('taxPercent', $value, $calculateMissing);
    }

    /**
     * @param float $value
     * @param bool  $calculateMissing [optional]
     *
     * @return $this
     */
    public function setTaxRate(float $value, $calculateMissing = true)
    {
        return $this->set('taxRate', $value, $calculateMissing);
    }

    /**
     * @return $this
     */
    protected function calculateGross()
    {
        if (!$this->has('gross')) {
            if ($this->has('net', 'tax')) {
                return $this->setGross($this->net + $this->tax);
            }
            if ($this->has('net', 'taxRate')) {
                return $this->setGross($this->net * $this->taxRate);
            }
            if ($this->has('tax', 'taxPercent') && $this->taxPercent) {
                return $this->setGross($this->tax ? $this->tax + $this->tax / $this->taxPercent * 100 : 0);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function calculateNet()
    {
        if (!$this->has('net')) {
            if ($this->has('gross', 'tax')) {
                return $this->setNet($this->gross - $this->tax);
            }
            if ($this->has('gross', 'taxRate') && $this->taxRate) {
                return $this->setNet($this->gross / $this->taxRate);
            }
            if ($this->has('tax', 'taxPercent') && $this->taxPercent) {
                return $this->setNet($this->tax ? $this->tax / $this->taxPercent * 100 : 0);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function calculateTax()
    {
        if (!$this->has('tax')) {
            if ($this->has('gross', 'net')) {
                return $this->setTax($this->gross - $this->net);
            }
            if ($this->has('gross', 'taxRate') && $this->taxRate) {
                return $this->setTax($this->gross - $this->gross / $this->taxRate);
            }
            if ($this->has('net', 'taxRate')) {
                return $this->setTax($this->net * $this->taxRate - $this->net);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function calculateTaxRate()
    {
        if (!$this->has('taxRate')) {
            if ($this->has('gross', 'net') && $this->net) {
                return $this->setTaxRate($this->gross / $this->net);
            }
            if ($this->has('gross', 'tax') && $this->gross) {
                return $this->setTaxRate($this->gross / ($this->gross - $this->tax));
            }
            if ($this->has('net', 'tax')) {
                return $this->setTaxRate($this->net ? ($this->net + $this->tax) / $this->net : 1.00);
            }
        }

        return $this;
    }

    /**
     * @param string|array $property
     * @param string[]     $properties [optional]
     *
     * @return bool
     */
    protected function has($property, ...$properties)
    {
        if (is_array($property)) {
            $properties = $property;
        } else {
            $properties[] = $property;
        }

        foreach ($properties as $property) {
            $property = $this->translateProperty($property);
            if (!isset($this->{$property})) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|array $property
     * @param string[]     $properties [optional]
     *
     * @return $this
     * @throws LogicException
     */
    protected function needed($property, ...$properties)
    {
        if (is_array($property)) {
            $properties = $property;
        } else {
            $properties[] = $property;
        }

        foreach ($properties as $property) {
            $this->validateProperty($property, true);
        }

        return $this;
    }

    /**
     * @param string $property
     *
     * @return string
     */
    protected function translateProperty(string $property)
    {
        return static::$translate[$property] ?? $property;
    }

    /**
     * @param string $property
     * @param bool   $needed [optional]
     *
     * @return $this
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    protected function validateProperty(string $property, bool $needed = false)
    {
        if (!property_exists($this, $property)) {
            throw new InvalidArgumentException("Property {$property} does not exist");
        }

        if ($needed && !isset($this->{$property})) {
            throw new LogicException("Property {$property} could not be calculated");
        }

        return $this;
    }
}
