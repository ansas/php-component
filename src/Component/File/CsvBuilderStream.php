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

use Psr\Http\Message\StreamInterface;

/**
 * Class CsvBuilderStream
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvBuilderStream extends CsvBuilderBase
{
    /**
     * @var int Lines written
     */
    protected $lines;

    /**
     * @var StreamInterface Stream CSV is written to
     */
    protected $stream;

    /**
     * Constructor.
     *
     * @param StreamInterface $stream
     */
    public function __construct(StreamInterface $stream)
    {
        $this->header = [];
        $this->lines  = 0;
        $this->stream = $stream;
    }

    /**
     * Create new instance.
     *
     * @param StreamInterface $stream
     *
     * @return static
     */
    public static function create(StreamInterface $stream)
    {
        return new static($stream);
    }

    /**
     * Add data (row) to CSV.
     *
     * @param array $data
     *
     * @return $this
     */
    public function addData(array $data)
    {
        if (!$this->hasHeader()) {
            $columns = array_keys($data);
            $this->setHeader($columns);
        }

        if (!$this->hasLines()) {
            $columns = array_keys($this->getHeader());
            $this->writeRow($columns);
        }

        $columns = $this->mergeData($data);
        $this->writeRow($columns);

        return $this;
    }

    /**
     * @return int
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->getStream()->getSize();
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return bool
     */
    public function hasLines()
    {
        return !!$this->lines;
    }

    /**
     * @param array $columns
     *
     * @return $this
     */
    protected function writeRow(array $columns)
    {
        $this->getStream()->write($this->buildRow($columns));
        $this->lines++;

        return $this;
    }

    /**
     * Merge data with header data (so columns always fit).
     *
     * @param array $data
     *
     * @return array
     */
    protected function mergeData(array $data)
    {
        $columns = [];
        foreach ($this->getHeader() as $key => $default) {
            $columns[] = isset($data[$key]) ? $data[$key] : $default;
        }

        return $columns;
    }
}
