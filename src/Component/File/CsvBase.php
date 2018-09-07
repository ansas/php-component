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
 * Class CSVBase
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvBase
{
    /**
     * @var string CSV delimiter
     */
    protected $delimiter = ";";

    /**
     * @var string CSV enclosure
     */
    protected $enclosure = "\"";

    /**
     * @var array Input/Output encoding
     */
    protected $encoding = [
        'input'  => 'UTF-8',
        'output' => 'UTF-8',
    ];

    /**
     * @var string CSV escape
     */
    protected $escape = "\\";

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Get decoding for CSV.
     *
     * @param string $direction
     *
     * @return string
     * @throws Exception
     */
    public function getEncoding($direction)
    {
        if (!isset($this->encoding[$direction])) {
            throw new Exception("Encoding direction invalid");
        }

        return $this->encoding[$direction];
    }

    /**
     * Get input decoding for CSV.
     *
     * @return string
     */
    public function getEncodingInput()
    {
        return $this->getEncoding('input');
    }

    /**
     * Get output decoding for CSV.
     *
     * @return string
     */
    public function getEncodingOutput()
    {
        return $this->getEncoding('output');
    }

    /**
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
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
     * Set encoding for CSV.
     *
     * @param string $direction
     * @param string $encoding
     *
     * @return $this
     * @throws Exception
     */
    public function setEncoding($direction, $encoding)
    {
        if (!isset($this->encoding[$direction])) {
            throw new Exception("Encoding direction invalid");
        }

        if (!$encoding) {
            throw new Exception("Encoding value invalid");
        } elseif (preg_match('/utf.?8/ui', $encoding)) {
            $encoding = 'UTF-8';
        } else {
            $encoding = mb_strtoupper($encoding);
        }

        $this->encoding[$direction] = $encoding;

        return $this;
    }

    /**
     * Set input decoding for CSV.
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function setEncodingInput($encoding)
    {
        return $this->setEncoding('input', $encoding);
    }

    /**
     * Set output decoding for CSV.
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function setEncodingOutput($encoding)
    {
        return $this->setEncoding('output', $encoding);
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
     * Converts encoding of $data from getEncodingInput() to getEncodingOutput().
     *
     * TODO optimize and add more than ISO <=> UTF-8 support
     *
     * @param array|string $data
     *
     * @return array|string
     */
    protected function convertEncoding($data)
    {
        if ($this->getEncodingInput() == $this->getEncodingOutput()) {
            return $data;
        }

        if (is_null($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertEncoding($value);
            }

            return $data;
        }

        if ($this->getEncodingOutput() == 'UTF-8') {
            return utf8_encode($data);
        }

        return utf8_decode($data);
    }
}
