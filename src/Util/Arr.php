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
 * @author  Ansas Meyer <ansas@ansas-meyer.de>
 */
class Arr
{
    public static function anyKey(array $data, string $key, bool $associative = true): array
    {
        $matches = [];

        $walk = function ($value, $index, $path) use ($key, $associative, &$walk, &$matches) {
            $path[] = $index;
            if (is_array($value)) {
                array_walk($value, $walk, $path);
            }

            if ($index == $key) {
                if ($associative) {
                    $matches[implode('.', $path)] = $value;
                } else {
                    $matches[] = $value;
                }
            }
        };

        array_walk($data, $walk, []);

        return $matches;
    }

    public static function onlyChanged(array $old, array $new): array
    {
        foreach (array_keys($old) as $key) {
            if (array_key_exists($key, $new)) {
                if (is_array($old[$key])) {
                    [$old[$key], $new[$key]] = static::onlyChanged($old[$key], $new[$key]);
                }
                if ($old[$key] === $new[$key]) {
                    unset($old[$key]);
                    unset($new[$key]);
                }
            }
        }

        return [$old, $new];
    }

    /**
     * Replace an array key.
     *
     * @param array $data     The data.
     * @param mixed $old
     * @param mixed $new
     * @param bool  $preserve [optional]
     */
    public static function replaceKey(array $data, $old, $new, $preserve = false): array
    {
        if (array_key_exists($old, $data)) {
            if ($preserve) {
                $keys       = array_keys($data);
                $pos        = array_search($old, $keys);
                $keys[$pos] = $new;
                $data       = array_combine($keys, $data);
            } else {
                $data[$new] = $data[$old];
                unset($data[$old]);
            }
        }

        return $data;
    }

    /**
     * Replace / map multiple array keys.
     *
     * @param array $data     The data.
     * @param array $map
     * @param bool  $preserve [optional]
     *
     * @return array The result.
     */
    public static function replaceKeys(array $data, array $map, $preserve = false)
    {
        foreach ($map as $old => $new) {
            $data = self::replaceKey($data, $old, $new, $preserve);
        }

        return $data;
    }

    /**
     * Replace an array value.
     *
     * @param array $data
     * @param mixed $old
     * @param mixed $new
     */
    public static function replaceValue(array $data, $old, $new): array
    {
        $pos = array_search($old, $data);
        if (false !== $pos) {
            $data[$pos] = $new;
        }

        return $data;
    }

    /**
     * Replace / map multiple array values.
     */
    public static function replaceValues(array $data, array $map): array
    {
        foreach ($map as $old => $new) {
            $data = self::replaceValue($data, $old, $new);
        }

        return $data;
    }

    /**
     * Append $old array by $new array (recursive) and override existing keys.
     */
    public static function merge(array $old, array $new): array
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
     * Move element with specified key to end of array.
     *
     * @param array $data The data.
     * @param mixed $key  The key to move to end of data.
     *
     * @return array The result.
     */
    public static function moveKeyToEnd(array $data, $key)
    {
        if (array_key_exists($key, $data)) {
            $data += array_splice($data, array_search($key, array_keys($data)), 1);
        }

        return $data;
    }

    /**
     * Get specified collection item.
     *
     * @param array $data
     * @param array $keys Keys to return in result.
     *
     * @return array
     */
    public static function only(array $data, array $keys)
    {
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Get specified value in array path.
     *
     * @param array        $data    The data.
     * @param array|string $path    The path of array keys.
     * @param mixed        $default [optional] The default value (if key does not exist).
     * @param string       $glue    [optional]
     *
     * @return mixed The item value.
     */
    public static function path(array $data, $path, $default = null, $glue = '.')
    {
        if (!is_array($path)) {
            $path = explode($glue, $path);
        }

        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return $default;
            }
            $data = $data[$key];
        }

        return $data;
    }

    /**
     * Does the path exist in the array?
     *
     * @param array        $data The array.
     * @param array|string $path The path of array keys.
     * @param string       $glue [optional]
     *
     * @return bool
     */
    public static function hasPath(array $data, $path, $glue = '.')
    {
        if (!is_array($path)) {
            $path = explode($glue, $path);
        }

        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return false;
            }
            $data = $data[$key];
        }

        return true;
    }

    /**
     * Get specified value in array path.
     *
     * @param array $data
     * @param int   $num [optional]
     *
     * @return mixed The random value(s).
     */
    public static function random(array $data, $num = 1)
    {
        $keys = array_rand($data, $num);

        if ($num == 1) {
            return $data[$keys];
        }

        $results = [];
        foreach ($keys as $key) {
            $results[] = $data[$key];
        }

        return $results;
    }

    /**
     * Increment specified value in array path.
     *
     * @param array        $data  The data.
     * @param array|string $path  The path of array keys.
     * @param mixed        $value The value to increment.
     * @param string       $glue  [optional]
     *
     * @return mixed The new array.
     */
    public static function incrementPath(array $data, $path, $value, $glue = '.')
    {
        return self::setPath($data, $path, self::path($data, $path, 0, $glue) + $value, $glue);
    }

    /**
     * Set specified value in array path.
     *
     * @param array        $data  The data.
     * @param array|string $path  The path of array keys.
     * @param mixed        $value The new value.
     * @param string       $glue  [optional]
     *
     * @return mixed The new array.
     */
    public static function setPath(array $data, $path, $value, $glue = '.')
    {
        if (!is_array($path)) {
            $path = explode($glue, $path);
        }

        $current = &$data;
        foreach ($path as $key) {
            if (!is_array($current)) {
                $current = [];
            }
            $current = &$current[$key];
        }

        $current = $value;

        return $data;
    }

    public static function unsetPath(array $data, array $path)
    {
        $current = &$data;

        $loop = 0;
        foreach ($path as $key) {
            if (++$loop != count($path)) {
                if (!is_array($current) || !isset($current[$key])) {
                    return $data;
                }
                $current = &$current[$key];
            } elseif (isset($current[$key])) {
                unset($current[$key]);
            }
        }

        return $data;
    }

    /**
     * Swap array keys of level 1 and level 2 in multidimensional array
     *
     * @param array $data
     *
     * @return array
     */
    public static function transpose(array $data): array
    {
        $return = [];
        foreach ($data as $key1 => $value1) {
            if (!is_array($value1)) {
                return $data;
            }
            foreach ($value1 as $key2 => $value2) {
                $return[$key2][$key1] = $value2;
            }
        }

        return $return;
    }
}
