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

use Exception;
use Traversable;

/**
 * Class CollectionOverride
 *
 * Manipulation class for any Collection that implements Traversable.
 *
 * The injected Collection can be manipulated using override() and restore() methods. This class is designed to
 * overwrite the original injected object in order to make changes globally effective.
 *
 * If you do not want to change the original collection you must clone it before injecting it. You can use the get()
 * helper method for retrieving the Collection at any time.
 *
 * @package Ansas\Component\Collection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CollectionOverride
{
    /**
     * @var Traversable Collection to override (manipulate).
     */
    protected $collection;

    /**
     * @var array Restore points (applied overrides).
     */
    protected $overrides;

    /**
     * CollectionOverride constructor.
     *
     * @param Traversable $collection
     */
    public function __construct(Traversable $collection)
    {
        $this->collection = $collection;
        $this->overrides = [];
    }

    /**
     * Create new instance.
     *
     * @param Traversable $collection
     *
     * @return static
     */
    public static function create(Traversable $collection)
    {
        return new static($collection);
    }

    /**
     * Returns the collection.
     *
     * @return Traversable
     */
    public function get()
    {
        return $this->collection;
    }

    /**
     * Override collection with provided $new data.
     *
     * @param Traversable|array $new
     *
     * @return $this
     * @throws Exception
     */
    public function override($new)
    {
        $old = iterator_to_array($this->collection);
        $new = $new instanceof Traversable ? iterator_to_array($this->collection) : $new;

        if (!is_array($new)) {
            throw new Exception("Argument must be an array or instance of Traversable");
        }

        // Create restore point
        $this->overrides[] = $old;

        // Override collection with new data
        $merged = $this->merge($old, $new);
        $this->apply($merged, $purge = false);

        return $this;
    }

    /**
     * Make changes (overrides) to collection permanent by deleting restore points.
     *
     * @return $this
     */
    public function persist()
    {
        $this->overrides = [];

        return $this;
    }

    /**
     * Restore collection to previous status before last override() call if available.
     *
     * @return bool Collection restored
     */
    public function restore()
    {
        // Restore settings before last override and delete override from stash
        if ($this->overrides) {
            $old = array_pop($this->overrides);
            $this->apply($old, $purge = true);

            return true;
        }

        return false;
    }

    /**
     * Apply (add) $new array to collection or replace it completely (when $purge is true).
     *
     * @param array $new
     * @param bool  $purge [optional]
     */
    protected function apply(array $new, $purge = false)
    {
        $old = $this->collection;

        // Delete old values
        // Note: make sure not to overwrite / detach original object to make changes be globally effective
        if ($purge) {
            foreach ($old as $key => $value) {
                unset($old[$key]);
            }
        }

        // Set new values
        foreach ($new as $key => $value) {
            $old[$key] = $value;
        }
    }

    /**
     * Append $old array by $new array (recursive) and override existing keys.
     *
     * @param array $old
     * @param array $new
     *
     * @return array
     */
    protected function merge(array $old, array $new)
    {
        foreach ($new as $key => $value) {
            if (isset($old[$key]) && is_array($old[$key]) && is_array($value)) {
                $old[$key] = $this->merge($old[$key], $value);
            } else {
                $old[$key] = $value;
            }
        }

        return $old;
    }
}
