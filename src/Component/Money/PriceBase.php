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
     * @return bool
     */
    public function isNegative()
    {
        return $this->getGross() < 0;
    }

    /**
     * @return bool
     */
    public function isPositive()
    {
        return $this->getGross() > 0;
    }

    /**
     * @return bool
     */
    public function isZero()
    {
        return $this->getGross() == 0;
    }

    /**
     * This method implements the JsonSerializable interface.
     *
     * @return array
     */
    function jsonSerialize()
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
     * @param int $options [optional] JSON_ constants bitmask (e. g. JSON_PRETTY_PRINT)
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
