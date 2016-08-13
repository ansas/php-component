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

use Exception;
use Generator;
use IteratorAggregate;
use SplFileInfo;
use SplFileObject;
use SplTempFileObject;

/**
 * Class CsvToArray
 *
 * @package Ansas\Component\Convert
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvIterator implements IteratorAggregate
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
     * @param string|SplFileInfo|SplFileObject $file
     *
     * @return CsvIterator A new instance
     */
    public static function create($file)
    {
        return new static($file);
    }

    /**
     * @param $string
     *
     * @return CsvIterator A new instance
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
     * @return array
     */
    public function asArray()
    {
        return iterator_to_array($this->getIterator(), false);
    }

    /**
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
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return Generator
     * @throws Exception
     */
    public function getIterator()
    {
        $this->reset();

        $header = $this->getHeader();

        while ($data = $this->getNextDataSet()) {
            if (count($header) != count($data)) {
                throw new Exception("Count mismatch in line {$this->getLineNumber()}");
            }
            $set = array_combine($header, $data);
            yield $set;
        }
    }

    /**
     * @return Generator
     */
    public function fetchArray()
    {
        yield from $this->getIterator();
    }

    /**
     * @return Generator
     */
    public function fetchObject()
    {
        foreach ($this->getIterator() as $set) {
            yield (object) $set;
        }
    }

    /**
     * @return int
     */
    public function getLineNumber()
    {
        return $this->line;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->header = null;
        $this->line = 0;
        $this->file->rewind();

        return $this;
    }

    /**
     * @param string $delimiter
     *
     * @return $this
     */
    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @param string $enclosure
     *
     * @return $this
     */
    public function setEnclosure(string $enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * @param string $escape
     *
     * @return $this
     */
    public function setEscape(string $escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * @param array $header
     *
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;

        return $this;
    }

    public function generateRows()
    {
        while ($line = $this->getNextDataSet()) {
            yield $line;
        }
    }

    /**
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

        return $set;
    }
}
