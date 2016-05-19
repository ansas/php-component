<?php

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Ansas\Component\Convert;

/**
 * ConvertPrice
 *
 * Convert "dirty" prices into Euro or Cent
 *
 * @author Ansas Meyer <webmaster@ansas-meyer.de>
 */
class ConvertPrice
{
    /** price format euro */
    const EURO = 'euro';

    /** price format cent */
    const CENT = 'cent';

    protected static $_instance = NULL;

    /**
     * @var array
     */
    protected static $formats = array(
        self::EURO => self::EURO,
        self::CENT => self::CENT,
    );

    /**
     * price
     * @var int
     */
    protected $price;

    /**
     * Constructor
     *
     * @param mixed  $price (optional) the price to convert
     * @param string $format (optional) the type of $price, default ConvertPrice::EURO
     */
    public function __construct($price = null, $format = self::EURO)
    {
        $this->clearPrice();

        if (null !== $price) {
            $this->setPrice($price, $format);
        }
    }

    /**
     * Output formatted price in euro if object is used in string context
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%.02f', $this->getPrice(self::EURO));
    }

    /**
     * Reset clear price
     *
     * @return $this
     */
    public function clearPrice()
    {
        $this->price = null;

        return $this;
    }

    /**
     * Get price
     *
     * @param string $format (optional) the type of $price, default ConvertPrice::EURO
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getPrice($format = self::EURO)
    {
        $this->validatePriceFormat($format);

        if ($format == self::EURO) {
            return $this->price / 100;
        }

        return $this->price;
    }

    /**
     * Set price after cutting out all unwanted chars
     *
     * This method converts (almost) every string into a price
     *
     * @param mixed  $price
     * @param string $format (optional) the type of $price, default ConvertPrice::EURO
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPrice($price, $format = self::EURO)
    {
        $this->validatePriceFormat($format);

        if ($format == self::EURO) {

            // remove: all not allowed chars
            $price = preg_replace('/[^0-9,-\.\+]/', '', $price);

            // sanitize: +/- at end of number
            $price = preg_replace('/^(.*)(-|\+)$/', '$2$1', $price);

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

        // cast: set to string before setting to int to avoid float inaccuracy
        $this->price = (int) (string) $price;

        return $this;
    }

    /**
     * Set price and return sanitized value at once
     *
     * @param mixed  $price
     * @param string $format (optional) the type of $price, default ConvertPrice::EURO
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function sanitize($price, $format = self::EURO)
    {
        return $this->setPrice($price, $format)->getPrice($format);
    }

    /**
     * Returns new or existing Singleton instance
     *
     * @return Singleton
     */
    final public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new ConvertPrice();
        }

        return static::$_instance;
    }

    /**
     * Check if price format is supported
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    private function validatePriceFormat($format)
    {
        if (!in_array($format, self::$formats)) {
            throw new \InvalidArgumentException('value of parameter $format not supported');
        }

        return $this;
    }
}
