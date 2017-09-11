<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\File;

/**
 * Class CsvReaderPartial
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvReaderPartial extends CsvReader
{
    /**
     * @var callable|null
     */
    protected $startPositionFinderCallback = null;

    /**
     * @var bool
     */
    protected $startPositionFound = false;

    /**
     * @var callable|null
     */
    protected $endPositionFinderCallback = null;

    /**
     * @var bool
     */
    protected $endPositionFound = false;

    /**
     * Callable must be <code>null</code> or of format <code>function (array $set): bool</code>
     *
     * @param callable|null $startPositionFinderCallback
     *
     * @return $this
     */
    public function setStartPositionFinderCallback(callable $startPositionFinderCallback = null)
    {
        $this->startPositionFinderCallback = $startPositionFinderCallback;

        return $this;
    }

    /**
     * Callable must be <code>null</code> or of format <code>function (array $set): bool</code>
     *
     * @param callable|null $endPositionFinderCallback
     *
     * @return $this
     */
    public function setEndPositionFinderCallback(callable $endPositionFinderCallback = null)
    {
        $this->endPositionFinderCallback = $endPositionFinderCallback;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStartPositionFound()
    {
        return $this->startPositionFound;
    }

    /**
     * @return bool
     */
    public function isEndPositionFound()
    {
        return $this->endPositionFound;
    }

    /**
     * @inheritdoc
     */
    protected function getNextDataSet()
    {
        $set = parent::getNextDataSet();

        // Only check if EOF (or blank line) is not reached
        if ($set) {
            if ($this->isStartPositionFound()) {
                // Start position found: now checking for end position
                $callback = $this->endPositionFinderCallback;
                if ($callback && $callback($set)) {
                    // Set to NULL to mark end of content
                    $set = null;

                    $this->endPositionFound = true;
                }
            } else {
                $callback = $this->startPositionFinderCallback;
                if ($callback) {
                    // Get next line if start position not found
                    if (!$callback($set)) {
                        return $this->getNextDataSet();
                    }

                    $this->startPositionFound = true;
                }
            }
        }

        return $set;
    }
}
