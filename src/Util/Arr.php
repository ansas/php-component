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
 * Class Arr
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Arr
{
    /**
     * Append $old array by $new array (recursive) and override existing keys.
     *
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    public static function merge(array $old, array $new)
    {
        foreach ($new as $key => $value) {
            if (isset($old[$key]) && is_array($old[$key]) && is_array($value)) {
                $old[$key] = self::merge($old[$key], $value);
            } else {
                $old[$key] = $value;
            }
        }

        return $old;
    }

    /**
     * Get specified collection item.
     *
     * @param  array $data    The data.
     * @param  array $path    The path of array keys.
     * @param  mixed $default [optional] The default value (if key does not exist).
     *
     * @return mixed The item value.
     */
    public static function path(array $data, $path, $default = null)
    {
        $path = (array) $path;

        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return $default;
            }
            $data = $data[$key];
        }

        return $data;
    }
}
