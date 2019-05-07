<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

/**
 * Class Obj
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Obj
{
    /**
     * Convert object to array.
     *
     * @param object $object
     *
     * @return array
     */
    public static function toArray($object)
    {
        return json_decode(json_encode((array) $object), true);
    }

    /**
     * Convert object to string.
     *
     * @param Obj $object
     *
     * @return string
     */
    public static function toString($object)
    {
        return (string) $object;
    }
}
