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

use JsonSerializable;

abstract class PriceBase implements JsonSerializable
{
    /**
     * @param string $property
     *
     * @return mixed
     */
    abstract public function get($property);

    /**
     * @param string $property [optional]
     *
     * @return int
     */
    public function getDirection($property = 'gross')
    {
        $property = $this->get($property);

        return $property > 0 ? 1 : ($property < 0 ? -1 : 0);
    }

    /**
     * @return float
     */
    public function getGross()
    {
        return $this->get('gross');
    }

    /**
     * @return float
     */
    public function getNet()
    {
        return $this->get('net');
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->get('tax');
    }

    /**
     * @param string $property [optional]
     *
     * @return bool
     */
    public function isNegative($property = 'gross')
    {
        return $this->get($property) < 0;
    }

    /**
     * @param string $property [optional]
     *
     * @return bool
     */
    public function isPositive($property = 'gross')
    {
        return $this->get($property) > 0;
    }

    /**
     * @param string $property [optional]
     *
     * @return bool
     */
    public function isZero($property = 'gross')
    {
        return $this->get($property) == 0;
    }

    /**
     * This method implements the JsonSerializable interface.
     */
    function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get object as array.
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Get object as JSON string.
     *
     * @param int $options [optional] JSON_ constants bitmask (like JSON_PRETTY_PRINT)
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
