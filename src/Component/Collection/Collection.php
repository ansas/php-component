<?php
/**
 * This file is part of the PHP components package.
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
use IteratorAggregate;
use Serializable;

/**
 * Class Collection
 *
 * Making handling of context data a bit easier. Collection can be accessed as
 * array or object. Elements can be set / added / retrieved / removed as single
 * elements or bundled as array.
 *
 * @package Ansas\Component\Collection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable, Serializable
{
    /**
     * @var array Holds complete collection data
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $items The initial items
     */
    public function __construct(array $items = [])
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
        return $this->has($key);
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
     * Adds item to collection for specified key
     * (converts item to array if key already exists).
     *
     * @param  mixed $key   The item key.
     * @param  mixed $value The item value to add / set.
     *
     * @return $this
     */
    public function add($key, $value)
    {
        if (!$this->has($key)) {
            $this->set($key, $value);
        } else {
            $this->data[$key]   = (array)$this->data[$key];
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
     * @param  array $items The items to append / overwrite to collection.
     *
     * @return $this
     */
    public function append(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
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
     * @param  mixed $default The default value (if key does not exist).
     *
     * @return mixed The item value.
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
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
     * @param  mixed $key The item key.
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
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
     * @param  mixed $key The item key.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
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
     * @return $this
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

        // Compare filter items by provided keys and return new array
        return array_intersect_key($this->data, array_flip($keys));
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
        if ($remove && $this->has($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Replaces the collection with the specified items.
     *
     * @param array $items The items to replace collection with.
     *
     * @return $this
     */
    public function replace(array $items)
    {
        $this->data = $items;

        return $this;
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
        $this->data[$key] = $value;

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
}
