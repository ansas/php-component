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
 * Class CsvBuilder
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvBuilder
{
    /**
     * @var array CSV header
     */
    protected $header;

    /**
     * @var array[] CSV data
     */
    protected $data;

    /**
     * @var string CSV delimiter
     */
    protected $delimiter = ";";

    /**
     * @var string CSV enclosure
     */
    protected $enclosure = "\"";

    /**
     * @var string CSV escape
     */
    protected $escape = "\\";

    /**
     * @var string CSV newline
     */
    protected $newline = "\n";

    /**
     * Constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->header = [];
        $this->data   = [];
    }

    /**
     * Convert object into CSV string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCsv();
    }

    /**
     * Create new instance.
     *
     * @return static
     */
    public static function create()
    {
        return new static();
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
        $this->mergeHeader(array_keys($data));
        $this->data[] = $data;

        return $this;
    }

    /**
     * Return built CSV string.
     *
     * @return string
     */
    public function getCsv()
    {
        $csv = "";

        // Build header
        $columns = array_keys($this->header);
        $columns = $this->sanitizeColumns($columns);
        $csv .= join($this->delimiter, $columns) . $this->newline;

        foreach ($this->data as $data) {
            $columns = [];
            foreach ($this->header as $key => $default) {
                $columns[] = isset($data[$key]) ? $data[$key] : $default;
            }
            $columns = $this->sanitizeColumns($columns);
            $csv .= join($this->delimiter, $columns) . $this->newline;
        }

        return $csv;
    }

    /**
     * Return CSV data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return CSV header as array.
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set CSV delimiter string.
     *
     * @param string $delimiter
     *
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Set CSV enclosure string.
     *
     * @param string $enclosure
     *
     * @return $this
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Set CSV escape string.
     *
     * @param string $escape
     *
     * @return $this
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

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
     * Add columns to header (if necessary).
     *
     * @param array $keys
     */
    protected function mergeHeader(array $keys)
    {
        foreach ($keys as $key) {
            if (!isset($this->header[$key])) {
                $this->header[$key] = '';
            }
        }
    }

    /* Add columns to header (if necessary).
    *
    * @param array $keys
    */
    protected function sanitizeColumns(array $columns)
    {
        if (!$this->enclosure) {
            return $columns;
        }

        foreach ($columns as &$column) {
            if (false !== strpos($column, $this->delimiter)
                || false !== strpos($column, $this->enclosure)
                || false !== strpos($column, $this->newline)
            ) {
                $column = str_replace($this->enclosure, $this->escape . $this->enclosure, $column);
                $column = $this->enclosure . $column . $this->enclosure;
            }
        }

        return $columns;
    }
}
