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
use Generator;
use IteratorAggregate;
use SplFileInfo;
use SplFileObject;
use SplTempFileObject;

/**
 * Class CsvReader
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvReader extends CsvBase implements IteratorAggregate
{
    /**
     * @var SplFileObject CSV file handle
     */
    protected $file;

    /**
     * @var array CSV header
     */
    protected $header;

    /**
     * @var int CSV line
     */
    protected $line;

    /**
     * @var array Malformed statistic
     */
    protected $malformedStatistic = [
        'malformed' => 0,
        'appended'  => 0,
        'truncated' => 0,
    ];

    /**
     * @var bool Ignore malformed lines
     */
    protected $ignoreMalformed = false;

    /**
     * @var bool Append (add) row columns to fit header columns
     */
    protected $appendRowsColumns = false;

    /**
     * @var bool Truncate (remove) row columns to fit header columns
     */
    protected $truncateRowsColumns = false;

    /**
     * CsvToArray constructor.
     *
     * @param string|SplFileInfo|SplFileObject $file
     *
     * @throws Exception
     */
    public function __construct($file)
    {
        if (!$file instanceof SplFileObject) {
            $file = (string) $file;
            $file = new SplFileObject($file);
        }

        $this->file = $file;
        $this->reset();
    }

    /**
     * Create new instance.
     *
     * @param string|SplFileInfo|SplFileObject $file
     *
     * @return static
     */
    public static function create($file)
    {
        return new static($file);
    }

    /**
     * Create new instance.
     *
     * @param $string
     *
     * @return static
     * @throws Exception
     */
    public static function createFromString($string)
    {
        $file = new SplTempFileObject(-1);

        if (null === $file->fwrite($string)) {
            throw new Exception("Cannot create file");
        }

        return new static($file);
    }

    /**
     * Return CSV as complete array (one element per line).
     *
     * @return array
     */
    public function asArray()
    {
        return iterator_to_array($this->getIterator(), false);
    }

    /**
     * Return CSV header as array.
     *
     * @return array
     * @throws Exception
     */
    public function getHeader()
    {
        if (null == $this->header) {
            $header = $this->getNextDataSet();
            if (null === $header) {
                throw new Exception("Cannot retrieve header");
            }
            $this->setHeader($header);
        }

        return $this->header;
    }

    /**
     * Get the iterator.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return Generator
     * @throws Exception
     */
    public function getIterator()
    {
        $header = $this->getHeader();

        while ($data = $this->getNextDataSet()) {
            $headerColumns = count($header);
            $dataColumns   = count($data);

            if ($headerColumns > $dataColumns && $this->appendRowsColumns) {
                $data = array_pad($data, $headerColumns, '');
                $this->malformedStatistic['appended']++;
            } elseif ($headerColumns < $dataColumns && $this->truncateRowsColumns) {
                $data = array_slice($data, 0, $headerColumns);
                $this->malformedStatistic['truncated']++;
            } elseif ($headerColumns != $dataColumns) {
                if ($this->ignoreMalformed) {
                    $this->malformedStatistic['malformed']++;
                    continue;
                }
                throw new Exception("Count mismatch in line {$this->getLineNumber()}, expected: {$headerColumns}, got: {$dataColumns}");
            }

            $set = array_combine($header, $data);
            yield $set;
        }
    }

    /**
     * Fetch CSV elements as array.
     *
     * @return Generator
     */
    public function fetchArray()
    {
        foreach ($this->getIterator() as $set) {
            yield $set;
        }

        // Note: "pure" PHP 7 syntax:
        // yield from $this->getIterator();
    }

    /**
     * Fetch CSV elements as object.
     *
     * @return Generator
     */
    public function fetchObject()
    {
        foreach ($this->getIterator() as $set) {
            yield (object) $set;
        }
    }

    /**
     * Get current line number in file.
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->line;
    }

    /**
     * Get malformed statistic.
     *
     * @return array
     */
    public function getMalformedStatistic()
    {
        return $this->malformedStatistic;
    }

    /**
     * Reset file.
     *
     * @return $this
     */
    public function reset()
    {
        $this->header = null;
        $this->line   = 0;
        $this->file->rewind();

        return $this;
    }

    /**
     * Set mode to append (add) row columns to fit header columns lines (or not).
     *
     * @param bool $appendRowsColumns
     *
     * @return $this
     */
    public function setAppendRowsColumns($appendRowsColumns)
    {
        $this->appendRowsColumns = (bool) $appendRowsColumns;

        return $this;
    }

    /**
     * Set mode to truncate (remove) row columns to fit header columns lines (or not).
     *
     * @param bool $truncateRowsColumns
     *
     * @return $this
     */
    public function setTruncateRowsColumns($truncateRowsColumns)
    {
        $this->truncateRowsColumns = (bool) $truncateRowsColumns;

        return $this;
    }

    /**
     * Set CSV header.
     *
     * Useful for CSV files without header line.
     *
     * @param array $header
     *
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Set mode to ignore malformed lines (or not).
     *
     * @param bool $ignoreMalformed
     *
     * @return $this
     */
    public function setIgnoreMalformed($ignoreMalformed)
    {
        $this->ignoreMalformed = (bool) $ignoreMalformed;

        return $this;
    }

    /**
     * Get and parse next data set (line).
     *
     * @return array|null
     * @throws Exception
     */
    protected function getNextDataSet()
    {
        $set = $this->file->fgetcsv($this->delimiter, $this->enclosure, $this->escape);

        if (1 === count($set) && null === $set[0]) {
            return null;
        }
        $this->line++;

        $set = $this->convertEncoding($set);

        return $set;
    }
}
