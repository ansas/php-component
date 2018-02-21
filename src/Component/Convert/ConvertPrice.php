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

use InvalidArgumentException;

/**
 * Class ConvertPrice
 *
 * Convert "dirty" prices into Euro or Cent.
 *
 * @package Ansas\Component\Convert
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ConvertPrice
{
    /** price format euro */
    const EURO = 'euro';

    /** price format cent */
    const CENT = 'cent';

    /**
     * @var ConvertPrice Instance for singleton usage.
     */
    protected static $instance = null;

    /**
     * @var array Allowed formats.
     */
    protected static $formats = [
        self::EURO => self::EURO,
        self::CENT => self::CENT,
    ];

    /**
     * @var int Price.
     */
    protected $price;

    /**
     * ConvertPrice constructor.
     *
     * @param mixed  $price  [optional] the price to convert
     * @param string $format [optional] the type of $price, default ConvertPrice::EURO
     */
    public function __construct($price = null, $format = self::EURO)
    {
        $this->clearPrice();

        if (null !== $price) {
            $this->setPrice($price, $format);
        }
    }

    /**
     * Output formatted price in euro if object is used in string context.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%.02f', $this->getPrice(self::EURO));
    }

    /**
     * Returns new or existing Singleton instance.
     *
     * @return static
     */
    final public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new ConvertPrice();
        }

        return static::$instance;
    }

    /**
     * Clear price.
     *
     * @return $this
     */
    public function clearPrice()
    {
        $this->price = null;

        return $this;
    }

    /**
     * Get price.
     *
     * @param string $format [optional] the type of $price, default ConvertPrice::EURO.
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getPrice($format = self::EURO)
    {
        $this->validatePriceFormat($format);

        if ($format == self::EURO) {
            return round($this->price / 100, 2);
        }

        return $this->price;
    }

    /**
     * Set price after cutting out all unwanted chars.
     *
     * This method converts (almost) every string into a price.
     *
     * @param mixed  $price
     * @param string $format [optional] the type of $price, default ConvertPrice::EURO
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPrice($price, $format = self::EURO)
    {
        $this->validatePriceFormat($format);

        // sanitize: price is null if value like -1.123E-11 provided
        if (preg_match('/^\-?\d+\.\d+E(\+|\-)\d+$/u', (string) $price)) {
            $this->price = 0;

            return $this;
        }

        if ($format == self::EURO) {
            // remove: all not allowed chars
            $price = preg_replace('/[^0-9,\-\.\+]/', '', $price);

            // sanitize: +/- at end of number
            $price = preg_replace('/^(.*)(\-|\+)$/', '$2$1', $price);

            // sanitize: , in price
            if (mb_strpos($price, ',') !== false) {
                if (preg_match('/,\./', $price)) {
                    $price = str_replace(',', '', $price);
                } else {
                    $price = str_replace('.', '', $price);
                    $price = str_replace(',', '.', $price);
                }
            }

            // convert: to internal int structure
            $price = $price * 100;
        }

        $this->price = round($price);

        return $this;
    }

    /**
     * Set price and return sanitized value at once.
     *
     * @param mixed  $price
     * @param string $format [optional] the type of $price, default ConvertPrice::EURO.
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function sanitize($price, $format = self::EURO)
    {
        return $this->setPrice($price, $format)->getPrice($format);
    }

    /**
     * Check if price format is supported.
     *
     * @param $format
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function validatePriceFormat($format)
    {
        if (!in_array($format, self::$formats)) {
            throw new InvalidArgumentException('value of parameter $format not supported');
        }

        return $this;
    }
}
