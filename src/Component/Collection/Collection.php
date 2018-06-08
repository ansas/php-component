<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Collection;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use Serializable;
use Traversable;

/**
 * Class Collection
 *
 * Making handling of context data a bit easier. Collection can be accessed as array or object. Elements can be
 * set / added / retrieved / removed as single elements or bundled as array.
 *
 * @package Ansas\Component\Collection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable, Serializable
{
    /** Sort collection by keys (ascending order) */
    const SORT_BY_KEYS_ASC = 1;

    /** Sort collection by keys (descending order) */
    const SORT_BY_KEYS_DESC = 2;

    /** Sort collection by values (ascending order) */
    const SORT_BY_VALUES_ASC = 4;

    /** Sort collection by values (descending order) */
    const SORT_BY_VALUES_DESC = 8;

    /** has check array_key_exists($key) mode */
    const HAS_EXISTS = 1;

    /** has check isset($key) mode */
    const HAS_ISSET = 2;

    /** has check empty($key) mode */
    const HAS_NONEMPTY = 4;

    /** has check strlen($key) mode */
    const HAS_LENGTH = 8;

    /**
     * @var array Holds complete collection data
     */
    protected $data = [];

    /**
     * Collection constructor.
     *
     * @param array|Traversable $items [optional] The initial items
     */
    public function __construct($items = [])
    {
        $this->replace($items);
    }

    /**
     * Get specified collection item.
     *
     * @param  mixed $key The item key.
     *
     * @return mixed The item value.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Check if specified collection item exists.
     *
     * @param  mixed $key The item key.
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key, self::HAS_ISSET);
    }

    /**
     * Set specified collection item.
     *
     * @param  mixed $key   The item key.
     * @param  mixed $value The item value.
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Converts object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * Removes specified collection item.
     *
     * @param  mixed $key The item key.
     *
     * @return $this
     */
    public function __unset($key)
    {
        return $this->remove($key);
    }

    /**
     * Create new instance.
     *
     * @param array|Traversable $items [optional] The initial items
     *
     * @return static
     */
    public static function create($items = [])
    {
        return new static($items);
    }

    /**
     * Adds item to collection for specified key
     * (converts item to array if key already exists).
     *
     * @param  mixed $key   The item key.
     * @param  mixed $value The item value to add / set.
     * @param int    $mode  The mode to compare if value exists [optional]
     *
     * @return $this
     */
    public function add($key, $value, $mode = self::HAS_ISSET)
    {
        if (!$this->has($key, $mode)) {
            $this->set($key, $value);
        } else {
            $key = $this->normalizeKey($key);

            $this->data[$key]   = (array) $this->data[$key];
            $this->data[$key][] = $value;
        }

        return $this;
    }

    /**
     * Get complete collection as array
     * (with original key => value pairs).
     *
     * @return array All items
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Appends specified items to collection
     * (overwrites existing keys).
     *
     * @param  array|Traversable $items The items to append / overwrite to collection.
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function append($items)
    {
        if (!is_array($items) && !$items instanceof Traversable) {
            throw new InvalidArgumentException("Argument must be an array or instance of Traversable");
        }

        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get collection as array.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->all();
    }

    /**
     * Get collection as json string.
     *
     * @param int $options [optional] JSON_ constants bitmask (e. g. JSON_PRETTY_PRINT)
     *
     * @return string
     */
    public function asJson($options = 0)
    {
        return json_encode($this->all(), $options);
    }

    /**
     * Get collection as object.
     *
     * @return object
     */
    public function asObject()
    {
        return (object) $this->all();
    }

    /**
     * Clears the collection (remove all items).
     *
     * @return $this
     */
    public function clear()
    {
        $this->replace([]);

        return $this;
    }

    /**
     * Count collection elements.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Get specified collection item.
     *
     * @param  mixed $key     The item key.
     * @param  mixed $default [optional] The default value (if key does not exist).
     * @param int    $mode    The mode to check if key exists [optional]
     *
     * @return mixed The item value.
     */
    public function get($key, $default = null, $mode = self::HAS_ISSET)
    {
        if (!$this->has($key, $mode)) {
            return $default;
        }

        $key = $this->normalizeKey($key);

        return $this->data[$key];
    }

    /**
     * Create an iterator to be able to traverse items via foreach.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Check if specified collection item exists.
     *
     * @param mixed $key  The item key.
     * @param int   $mode The mode to check if key exists [optional]
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($key, $mode = self::HAS_EXISTS)
    {
        $key = $this->normalizeKey($key);

        switch ($mode) {
            case self::HAS_EXISTS:
                return array_key_exists($key, $this->data);

            case self::HAS_ISSET:
                return isset($this->data[$key]);

            case self::HAS_NONEMPTY:
                return !empty($this->data[$key]);

            case self::HAS_LENGTH:
                return isset($this->data[$key]) && strlen($this->data[$key]);

            default:
                throw new InvalidArgumentException("Mode {$mode} not supported");
        }
    }

    /**
     * Check if collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->count();
    }

    /**
     * Get collection keys.
     *
     * @return array All item keys.
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Check if specified collection item exists.
     *
     * @param  mixed  $key      The item key.
     * @param  string $regex
     * @param  array  &$matches [optional]
     *
     * @return bool
     */
    public function matches($key, $regex, &$matches = [])
    {
        if (!$this->has($key, self::HAS_ISSET)) {
            return false;
        }

        return (bool) preg_match($regex, $this->get($key), $matches);
    }

    /**
     * Get specified collection item.
     *
     * @param  mixed $key  The item key.
     * @param  int   $mode The mode to check if key exists [optional]
     *
     * @return mixed The item value.
     * @throws Exception
     */
    public function need($key, $mode = self::HAS_ISSET)
    {
        if (!$this->has($key, $mode)) {
            throw new Exception("Key {$key} is required");
        }

        return $this->get($key);
    }

    /**
     * Check if specified collection item exists.
     *
     * @param  mixed $key The item key.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key, self::HAS_EXISTS);
    }

    /**
     * Get specified collection item.
     *
     * @param  mixed $key The item key.
     *
     * @return mixed The item value.
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set specified collection item.
     *
     * @param  mixed $key   The item key.
     * @param  mixed $value The item value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Removes specified collection item.
     *
     * @param  mixed $key The item key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Get filtered collection as array
     * (with original key => value pairs).
     * $keys can be an array or a comma separated string of keys.
     *
     * @param  mixed $keys The item keys to export.
     *
     * @return array Filtered items.
     */
    public function only($keys)
    {
        // Convert $keys to array if necessary
        if (!is_array($keys)) {
            $keys = preg_split("/, */", $keys, -1, PREG_SPLIT_NO_EMPTY);
        }

        foreach ($keys as $id => $key) {
            $keys[$id] = $this->normalizeKey($key);
        }

        // Compare filter items by provided keys and return new array
        return array_intersect_key($this->data, array_flip($keys));
    }

    /**
     * Get specified collection item.
     *
     * @param  array $path    The path of array keys.
     * @param  mixed $default [optional] The default value (if key does not exist).
     *
     * @return mixed The item value.
     */
    public function path($path, $default = null)
    {
        $data = $this->data;
        $path = (array) $path;

        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return $default;
            }
            $data = $data[$key];
        }

        return $data;
    }

    /**
     * Removes specified collection item.
     *
     * @param mixed $key    The item key.
     * @param bool  $remove [optional] Conditional remove statement.
     *
     * @return $this
     */
    public function remove($key, $remove = true)
    {
        if ($remove && $this->has($key, self::HAS_EXISTS)) {
            $key = $this->normalizeKey($key);
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Removes empty collection items.
     *
     * @param array $considerEmpty [optional]
     *
     * @return $this
     */
    public function removeEmpty($considerEmpty = [''])
    {
        foreach ($this->all() as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            foreach ($considerEmpty as $empty) {
                if ($value === $empty) {
                    $this->remove($key);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Replaces the collection with the specified items.
     *
     * @param array|Traversable $items The items to replace collection with.
     *
     * @return $this
     */
    public function replace($items)
    {
        $this->data = [];

        return $this->append($items);
    }

    /**
     * Convert object into string to make it storable (freeze, store).
     *
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->data);
    }

    /**
     * Set specified collection item.
     *
     * @param  mixed $key   The item key.
     * @param  mixed $value The item value.
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $key = $this->normalizeKey($key);

        if (null === $key) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Set specified collection item.
     *
     * @param  mixed $key The item key. [optional]
     *
     * @return $this
     */
    public function trim($key = null)
    {
        if (null === $key) {
            foreach ($this->all() as $key => $value) {
                if (is_scalar($value)) {
                    $this->set($key, trim($value));
                }
            }
        } else {
            $value = $this->get($key);
            if (is_scalar($value)) {
                $this->set($key, trim($value));
            }
        }

        return $this;
    }

    /**
     * Sort collection.
     *
     * @param int $sortBy    Sort by flag (see self::SORT_ constants)
     * @param int $sortFlags Sort flags (see PHP SORT_ sonstants)
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function sort($sortBy = self::SORT_BY_KEYS_ASC, $sortFlags = SORT_REGULAR)
    {
        $sortFunctions = [
            self::SORT_BY_KEYS_ASC    => 'ksort',
            self::SORT_BY_KEYS_DESC   => 'krsort',
            self::SORT_BY_VALUES_ASC  => 'asort',
            self::SORT_BY_VALUES_DESC => 'arsort',
        ];

        if (!isset($sortFunctions[$sortBy])) {
            throw new InvalidArgumentException("SortBy {$sortBy} not supported");
        }

        $function = $sortFunctions[$sortBy];
        $function($this->data, $sortFlags);

        return $this;
    }

    /**
     * Switch values of two keys.
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return $this
     */
    public function switch($a, $b)
    {
        $oldA = $this->get($a);
        $oldB = $this->get($b);

        $this->set($a, $oldB);
        $this->set($b, $oldA);

        return $this;
    }

    /**
     * Convert string back into object from storage (unfreeze, restore).
     *
     * @param  string $data
     *
     * @return void
     */
    public function unserialize($data)
    {
        $this->data = json_decode($data);
    }

    /**
     * Get collection values.
     *
     * @return array All item values.
     */
    public function values()
    {
        return array_values($this->data);
    }

    /**
     * Normalize key.
     *
     * Useful in child classes to make keys upper case for example.
     *
     * @param string $key
     *
     * @return string
     */
    protected function normalizeKey($key)
    {
        return $key;
    }
}
