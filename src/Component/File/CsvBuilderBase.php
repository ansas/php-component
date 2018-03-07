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

use Exception;

/**
 * Class CsvBuilderBase
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@preigu.com>
 */
abstract class CsvBuilderBase extends CsvBase
{
    /**
     * @var callable Callback function
     */
    protected $callback = null;

    /**
     * @var array CSV header
     */
    protected $header;

    /**
     * @var string CSV newline
     */
    protected $newline = "\n";

    /**
     * @var bool Set mode to skip printing header in CSV
     */
    protected $withoutHeader = false;

    /**
     * Add data (row) to CSV.
     *
     * @param array $data
     *
     * @return $this
     */
    abstract public function addData(array $data);

    /**
     * Return size of build CSV in byte.
     *
     * @return int
     */
    abstract public function getSize();

    /**
     * Return built CSV string.
     *
     * @return string
     */
    abstract public function getCsv();

    /**
     * @return callable|null
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Return CSV header as array.
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header ?: [];
    }

    /**
     * @return string
     */
    public function getNewline()
    {
        return $this->newline;
    }

    /**
     * Skip printing header in CSV (or not).
     *
     * @return bool
     */
    public function getWithoutHeader()
    {
        return $this->withoutHeader;
    }

    /**
     * @return bool
     */
    public function hasHeader()
    {
        return !!$this->header;
    }

    /**
     * Set callback function run for every entry before added to CSV.
     *
     * Callable must take old column value as first parameter and return new (sanitized / filtered) column parameter.
     *
     * @param callable $callback [optional]
     *
     * @return $this
     * @throws Exception
     *
     */
    public function setCallback($callback = null)
    {
        if (!is_null($callback) && !is_callable($callback)) {
            throw new Exception("callback must be of type callable or null");
        }

        $this->callback = $callback;

        return $this;
    }

    /**
     * Add multiple data (rows) to CSV.
     *
     * @param array[] $rows
     *
     * @return $this
     */
    public function setData(array $rows)
    {
        foreach ($rows as $data) {
            $this->addData($data);
        }

        return $this;
    }

    /**
     * Set CSV headers.
     *
     * @param array $header
     *
     * @return $this
     */
    public function setHeader(array $header)
    {
        if (is_numeric(key($header))) {
            $header = array_fill_keys($header, '');
        }
        $this->header = $header;

        return $this;
    }

    /**
     * Set newline string.
     *
     * @param string $newline
     *
     * @return $this
     */
    public function setNewline($newline)
    {
        $this->newline = $newline;

        return $this;
    }

    /**
     * Set mode to skip printing header in CSV (or not).
     *
     * @param bool $withoutHeader
     *
     * @return $this
     */
    public function setWithoutHeader($withoutHeader)
    {
        $this->withoutHeader = (bool) $withoutHeader;

        return $this;
    }

    /**
     * @param array $columns
     * @param bool  $sanitize [optional]
     *
     * @return string
     */
    protected function buildRow(array $columns, $sanitize = true)
    {
        if ($sanitize) {
            $columns = $this->sanitizeColumns($columns);
        }

        $row = join($this->delimiter, $columns) . $this->newline;

        $row = $this->convertEncoding($row);

        return $row;
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    protected function sanitizeColumns(array $columns)
    {
        $callback = $this->getCallback();
        if ($callback) {
            foreach ($columns as $key => $value) {
                $columns[$key] = $callback($value);
            }
        }

        if (!$this->enclosure) {
            return $columns;
        }

        foreach ($columns as $key => $value) {
            if (false !== strpos($value, $this->delimiter)
                || false !== strpos($value, $this->enclosure)
                || false !== strpos($value, $this->newline)
            ) {
                $value = str_replace($this->enclosure, $this->escape . $this->enclosure, $value);
                $value = $this->enclosure . $value . $this->enclosure;

                $columns[$key] = $value;
            }
        }

        return $columns;
    }
}
