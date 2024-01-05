<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Iterator;

use Countable;
use Generator;
use IteratorAggregate;

class CollectionIterator implements IteratorAggregate, Countable
{
    protected iterable $iterator;

    protected int $position;

    protected int $positions;

    public function __construct(iterable $iterator, int $count = null)
    {
        $this->iterator = $iterator;

        if (null !== $count) {
            $this->positions = $count;
        } elseif (is_object($iterator) && method_exists($iterator, 'count')) {
            $this->positions = $iterator->count();
        } elseif($iterator instanceof \Traversable) {
            $this->positions = iterator_count($iterator);
        } elseif(is_array($iterator)) {
            $this->positions = count($iterator);
        }

        $this->reset();
    }

    public static function create(iterable $iterator, int $count = null): self
    {
        return new static($iterator, $count);
    }

    /**
     * Returns the number of elements.
     *
     * This method implements the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return $this->getTotal();
    }

    /**
     * Get current loop call index (starting with 1).
     *
     * @return int
     */
    public function getIndex()
    {
        return (int) $this->position;
    }

    /**
     * Get the iterator.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return Generator
     */
    public function getIterator()
    {
        $this->reset();

        foreach ($this->iterator as $key => $value) {
            $this->position++;
            yield $key => $value;
        }
    }

    /**
     * Returns the number of elements left.
     *
     * @return int
     */
    public function getLeft()
    {
        return $this->getTotal() - $this->getIndex();
    }

    /**
     * Returns the number of elements.
     *
     * @return int
     */
    public function getTotal()
    {
        return (int) $this->positions;
    }

    /**
     * Check if loop calls left.
     *
     * @return bool
     */
    public function hasNext()
    {
        return !$this->isLast();
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->positions;
    }

    /**
     * Check if it's an even loop call.
     *
     * @return bool
     */
    public function isEven()
    {
        return $this->getIndex() % 2 == 0;
    }

    /**
     * Check if loop call index is a part of $split.
     *
     * Useful for e. g. flushing a buffer every x calls or using this in combination with progress profiling.
     *
     * @param int $split
     *
     * @return bool
     */
    public function isEvery(int $split)
    {
        return $this->getIndex() % $split == 0;
    }

    /**
     * Check if loop call index is a part of $split or if it is the last loop call.
     *
     * Useful if you want to e. g. flush a buffer every x calls and if it is the last call.
     *
     * @param int $split
     *
     * @return bool
     */
    public function isEveryOrLast(int $split)
    {
        return $this->isEvery($split) || $this->isLast();
    }

    /**
     * Check if it's the first loop call.
     *
     * @return bool
     */
    public function isFirst()
    {
        return $this->getIndex() == 1;
    }

    /**
     * Check if it's the last loop call.
     *
     * @return bool
     */
    public function isLast()
    {
        return $this->getIndex() == $this->getTotal();
    }

    /**
     * Check if it's an odd loop call.
     *
     * @return bool
     */
    public function isOdd()
    {
        return !$this->isEven();
    }

    /**
     * Check if it's the first and only (= last) loop call.
     *
     * @return bool
     */
    public function isOnly()
    {
        return $this->isFirst() && $this->isLast();
    }

    /**
     * Reset counter.
     */
    protected function reset()
    {
        $this->position = 0;
    }
}
