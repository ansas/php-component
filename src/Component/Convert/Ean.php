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

use Exception;

/**
 * Class Ean
 *
 * Takes EAN, UCP or ISBN13 and defines type. Adds correct formatted output for these types.
 *
 * Also you can create objects from ISBN10 and convert it back.
 *
 * @package Ansas\Component\Convert
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Ean
{
    /** supported types */
    const EAN    = 'EAN';
    const ISBN13 = 'ISBN-13';
    const ISBN10 = 'ISBN-10';
    const UPC    = 'UPC';

    /**
     * @var array Allowed formats.
     */
    protected static $types = [
        self::EAN    => self::EAN,
        self::ISBN13 => self::ISBN13,
        self::ISBN10 => self::ISBN10,
        self::UPC    => self::UPC,
    ];

    /**
     * @var string type.
     */
    protected $type;

    /**
     * @var int value
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param int $value ean, upc or isbn13
     */
    public function __construct($value)
    {
        $this->parseValue($value);
    }

    /**
     * Output formatted ean.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Create new instance.
     *
     * @param int $value ean, upc or isbn13
     *
     * @return static
     */
    public static function create($value)
    {
        return new static($value);
    }

    /**
     * Create new instance.
     *
     * @param mixed $value isbn10
     *
     * @return static
     * @throws Exception
     */
    public static function createFromIsbn10($value)
    {
        $value = static::fromIsbn10($value);

        if (!$value) {
            throw new Exception("Provided value is not a valid isbn10");
        }

        return new static($value);
    }

    /**
     * Convert isbn10 to isbn13 (ean).
     *
     * @param string $isbn    isbn10
     * @param mixed  $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public static function fromIsbn10($isbn, $onError = null)
    {
        // Sanitize
        $isbn = preg_replace('/[^0-9Xx]/', '', $isbn);
        $isbn = strlen($isbn) == 10 ? substr($isbn, 0, 9) : $isbn;

        // Validate
        if (strlen($isbn) != 9) {
            return $onError;
        }

        // Convert
        $ean = '978' . $isbn;
        $ean = static::withCheckDigitForEan($ean);

        return $ean ?: $onError;
    }

    /**
     * Convert isbn13 to isbn13 (ean).
     *
     * @param string $isbn    isbn13
     * @param mixed  $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public static function fromIsbn13($isbn, $onError = null)
    {
        // Sanitize
        $isbn = preg_replace('/[^0-9]/', '', $isbn);

        // Validate
        if (strlen($isbn) != 13) {
            return $onError;
        }

        return $isbn;
    }

    /**
     * Convert isbn13 to isbn10.
     *
     * @param string $ean     isbn13
     * @param mixed  $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public static function toIsbn10($ean, $onError = null)
    {
        // Sanitize
        $ean = preg_replace('/[^0-9]/', '', $ean);

        // Validate
        if (!preg_match('/^978(\d{9})\d?$/', $ean, $found)) {
            return $onError;
        }

        // Convert
        $isbn = $found[1];
        $isbn = static::withCheckDigitForIsbn10($isbn);

        return $isbn ?: $onError;
    }

    /**
     * (Re)Calculate check digit of given $ean and return complete ean with (new) check digit.
     *
     * @param int $ean
     *
     * @return string|null
     */
    public static function withCheckDigitForEan($ean)
    {
        // Sanitize
        $ean = (int) $ean;
        $ean = substr($ean, 0, 12);

        // Validate
        if (strlen($ean) != 12) {
            return null;
        }

        // Calculate (sum)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $ean{$i} * ($i % 2 ? 3 : 1);
        }

        // Calculate (check digit)
        $checkDigit = ceil($sum / 10) * 10 - $sum;

        return $ean . $checkDigit;
    }

    /**
     * Calculate and return check digit of given $isbn.
     *
     * @param mixed $isbn
     *
     * @return string|null
     */
    public static function withCheckDigitForIsbn10($isbn)
    {
        // Sanitize
        $isbn = preg_replace('/[^0-9]/', '', $isbn);
        $isbn = substr($isbn, 0, 9);

        // Validate
        if (strlen($isbn) != 9) {
            return null;
        }

        // Calculate (sum)
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (10 - $i) * $isbn{$i};
        }

        // Calculate (check digit)
        $checkDigit = 11 - ($sum % 11);
        $checkDigit = $checkDigit == 10 ? "X" : ($checkDigit == 11 ? 0 : $checkDigit);

        return $isbn . $checkDigit;
    }

    /**
     * @param mixed $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public function asEan($onError = null)
    {
        return $this->value ? sprintf('%013s', $this->value) : $onError;
    }

    /**
     * @param mixed $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public function asIsbn10($onError = null)
    {
        return static::toIsbn10($this->value, $onError);
    }

    /**
     * @param mixed $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public function asIsbn13($onError = null)
    {
        return $this->type == self::ISBN13 ? $this->asEan($onError) : $onError;
    }

    /**
     * @param mixed $onError [optional] string to return if value is not specified type.
     *
     * @return mixed
     */
    public function asUpc($onError = null)
    {
        return $this->type == self::UPC ? sprintf('%012s', $this->value) : $onError;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->getValueByType($this->getType());
    }

    /**
     * @param $type
     *
     * @return string|null
     * @throws Exception
     */
    public function getValueByType($type)
    {
        $type = strtoupper($type);

        switch ($type) {
            case static::EAN:
                return $this->asEan();

            case static::ISBN10:
                return $this->asIsbn10();

            case static::ISBN13:
                return $this->asIsbn13();

            case static::UPC:
                return $this->asUpc();

            default:
                throw new Exception("Type {$type} not supported");
        }
    }

    /**
     * @return array
     */
    public function getValueList()
    {
        $list = [];

        foreach (static::$types as $type) {
            $value = $this->getValueByType($type);
            if ($value) {
                $list[$type] = $value;
            }
        }

        return $list;
    }

    /**
     * @return int|null
     */
    public function getValueRaw()
    {
        return $this->value;
    }

    /**
     * @param int $value ean, upc or isbn13
     *
     * @throws Exception
     */
    protected function parseValue($value)
    {
        // Sanitize
        $value  = (int) $value;
        $length = strlen($value);

        // Validate
        if ($length < 6 || $length > 13) {
            throw new Exception("No valid EAN, UPC or ISBN13 provided");
        }

        // Set value
        $this->value = $value;

        // Set type
        if ($length < 13) {
            $this->type = static::UPC;
        } elseif (preg_match('/^97(?:8|9)/', $value)) {
            $this->type = static::ISBN13;
        } else {
            $this->type = static::EAN;
        }
    }
}
