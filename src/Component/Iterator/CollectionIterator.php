<?php
/**
 * This file is part of the PHP components package.
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
use Traversable;

/**
 * Class CollectionIterator
 *
 * @package Ansas\Component\Iterator
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CollectionIterator implements IteratorAggregate, Countable
{
    /**
     * @var Traversable Iterator
     */
    protected $iterator;

    /**
     * @var int Current position
     */
    protected $position;

    /**
     * @var int Total positions
     */
    protected $positions;

    /**
     * CollectionIterator constructor.
     *
     * @param Traversable $iterator
     */
    public function __construct(Traversable $iterator)
    {
        $this->iterator  = $iterator;
        $this->positions = iterator_count($iterator);
        $this->reset();
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
        return $this->position;
    }

    /**
     * @return Generator
     */
    public function getIterator()
    {
        $this->reset();

        foreach ($this->iterator as $item) {
            $this->position++;
            yield $item;
        }
    }

    /**
     * Returns the number of elements.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->positions;
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
     * @param int $split
     *
     * @return bool
     */
    public function isEvery(int $split)
    {
        return $this->getIndex() % $split == 0;
    }

    /**
     * Check if it's the first loop call.
     *
     * @return bool
     */
    public function isFirst()
    {
        return $this->getIndex() === 1;
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
     * Reset counter.
     */
    protected function reset()
    {
        $this->position = 0;
    }
}
