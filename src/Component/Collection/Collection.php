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

use Ansas\Util\Arr;
use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Class Collection
 *
 * Making handling of context data a bit easier. Collection can be accessed as array or object. Elements can be
 * set / added / retrieved / removed as single elements or bundled as array.
 *
 * @package Ansas\Component\Collection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /** Sort collection by keys (ascending order) */
    const SORT_BY_KEYS_ASC = 1;

    /** Sort collection by keys (descending order) */
    const SORT_BY_KEYS_DESC = 2;

    /** Sort collection by values (ascending order) */
    const SORT_BY_VALUES_ASC = 4;

    /** Sort collection by values (descending order) */
    const SORT_BY_VALUES_DESC = 8;

    /** has check "array key exists" mode */
    const HAS_EXISTS = 1;

    /** has check "isset" mode */
    const HAS_ISSET = 2;

    /** has check "empty" mode */
    const HAS_NONEMPTY = 4;

    /** has check "string length" mode */
    const HAS_LENGTH = 8;

    /**
     * @var array Holds complete collection data
     */
    protected array $data = [];

    /**
     * Collection constructor.
     */
    public function __construct(iterable $items = [])
    {
        $this->replace($items);
    }

    /**
     * Get specified collection item.
     */
    public function __get(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Check if specified collection item exists.
     */
    public function __isset(mixed $key): bool
    {
        return $this->has($key, self::HAS_ISSET);
    }

    /**
     * Set specified collection item.
     */
    public function __set(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Converts object to string.
     */
    public function __toString(): string
    {
        return (string) $this->asJson();
    }

    /**
     * Removes specified collection item.
     */
    public function __unset(mixed $key)
    {
        $this->remove($key);
    }

    /**
     * Convert object into string to make it storable (freeze, store).
     */
    public function __serialize(): array
    {
        return ['data' => $this->asJson()];
    }

    /**
     * Convert string back into object from storage (unfreeze, restore).
     *
     * @noinspection SpellCheckingInspection
     */
    public function __unserialize(array $data): void
    {
        $this->data = $data['data'];
    }

    /**
     * Create new instance.
     */
    public static function create(iterable $items = []): static
    {
        return new static($items);
    }

    /**
     * Adds item to collection for specified key
     * (converts item to array if key already exists).
     */
    public function add(mixed $key, mixed $value, int $mode = self::HAS_ISSET): static
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
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Appends specified items to collection (overwrites existing keys).
     */
    public function append(iterable $items): static
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get collection as array.
     */
    public function asArray(): array
    {
        return $this->all();
    }

    /**
     * Get collection as json string.
     */
    public function asJson(int $options = 0): string|false
    {
        return json_encode($this->all(), $options);
    }

    /**
     * Get collection as object.
     */
    public function asObject(): object
    {
        return (object) $this->all();
    }

    /**
     * Clears the collection (remove all items).
     */
    public function clear(): static
    {
        $this->replace([]);

        return $this;
    }

    /**
     * Count collection elements.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get specified collection item.
     */
    public function get(mixed $key, mixed $default = null, int $mode = self::HAS_ISSET): mixed
    {
        if (!$this->has($key, $mode)) {
            return $default;
        }

        $key = $this->normalizeKey($key);

        return $this->data[$key];
    }

    /**
     * Create an iterator to be able to traverse items via foreach.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Check if specified collection item exists.
     *
     * @throws InvalidArgumentException
     */
    public function has(mixed $key, int $mode = self::HAS_EXISTS): bool
    {
        $key = $this->normalizeKey($key);

        return match ($mode) {
            self::HAS_EXISTS   => array_key_exists($key, $this->data),
            self::HAS_ISSET    => isset($this->data[$key]),
            self::HAS_NONEMPTY => !empty($this->data[$key]),
            self::HAS_LENGTH   => isset($this->data[$key]) && strlen($this->data[$key]),
            default            => throw new InvalidArgumentException("Mode {$mode} not supported"),
        };
    }

    /**
     * Check if collection is empty.
     */
    public function isEmpty(): bool
    {
        return !$this->count();
    }

    /**
     * Get collection keys.
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Check if specified collection item exists.
     */
    public function matches(mixed $key, string $regex, ?array &$matches = []): bool
    {
        if (!$this->has($key, self::HAS_ISSET)) {
            return false;
        }

        return (bool) preg_match($regex, $this->get($key), $matches);
    }

    /**
     * Get specified collection item.
     *
     * @throws Exception
     */
    public function need(mixed $key, int $mode = self::HAS_ISSET): mixed
    {
        if (!$this->has($key, $mode)) {
            throw new Exception("Key {$key} is required");
        }

        return $this->get($key);
    }

    /**
     * Check if specified collection item exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get specified collection item.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set specified collection item.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Removes specified collection item.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Get filtered collection as array
     * (with original key => value pairs).
     * $keys can be an array or a comma separated string of keys.
     */
    public function only(array|string $keys): array
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
     */
    public function path(array|string $path, mixed $default = null, string $glue = '.'): mixed
    {
        return Arr::path($this->data, $path, $default, $glue);
    }

    /**
     * Removes specified collection item.
     */
    public function remove(mixed $key, bool $remove = true): static
    {
        if ($remove && $this->has($key)) {
            $key = $this->normalizeKey($key);
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Removes empty collection items.
     */
    public function removeEmpty(array $considerEmpty = ['']): static
    {
        foreach ($this->all() as $key => $value) {
            if (!is_scalar($value) && !is_null($value)) {
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
     */
    public function replace(iterable $items): static
    {
        $this->data = [];

        return $this->append($items);
    }

    /**
     * Set specified collection item.
     */
    public function set(mixed $key, mixed $value): static
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
     */
    public function trim(mixed $key = null): static
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
     * @param int $sortFlags Sort flags (see PHP SORT_ constants)
     *
     * @throws InvalidArgumentException
     */
    public function sort(int $sortBy = self::SORT_BY_KEYS_ASC, int $sortFlags = SORT_REGULAR): static
    {
        /** @noinspection SpellCheckingInspection */
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
     * Swap / switch values of two keys.
     */
    public function swap(mixed $a, mixed $b): static
    {
        $oldA = $this->get($a);
        $oldB = $this->get($b);

        $this->set($a, $oldB);
        $this->set($b, $oldA);

        return $this;
    }

    /**
     * Get collection values.
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * Normalize key.
     *
     * Useful in child classes to make keys upper case for example.
     */
    protected function normalizeKey(mixed $key): mixed
    {
        return $key;
    }
}
