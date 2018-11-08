<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Monolog\Processor;

use Exception;
use Throwable;
use Traversable;

/**
 * Class CleanupProcessor
 *
 * @package Ansas\Monolog\Processor
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CleanupProcessor
{
    /**
     * @var array|string Holds tags to strip.
     */
    protected $stripTags;

    /**
     * @var bool Flag remove null values.
     */
    protected $removeNull = true;

    /**
     * @var bool Flag convert Throwable to array.
     */
    protected $convertThrowable = true;

    /**
     * @var bool Flag convert Traversable to array.
     */
    protected $convertTraversable = true;

    /**
     * CleanupProcessor constructor.
     *
     * @param array $strip
     *
     * @throws Exception
     */
    public function __construct($strip = [])
    {
        if (!is_scalar($strip) && !is_array($strip)) {
            throw new Exception("Strip must be scalar or array of scalar.");
        }
        $this->stripTags = $strip;
    }

    /**
     * Invoke class.
     *
     * @param  array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        return $this->cleanup($record);
    }

    /**
     * Cleanup routine.
     *
     * @param  mixed $record
     *
     * @return mixed
     */
    protected function cleanup($record)
    {
        // Translate Throwable (Exception || Error) object into simple array
        if ($this->convertThrowable && $record instanceof Throwable) {
            $record = [
                'message'  => $record->getMessage(),
                'line'     => $record->getLine(),
                'file'     => $record->getFile(),
                'class'    => get_class($record),
                'code'     => $record->getCode(),
                'trace'    => $record->getTrace(),
                'previous' => $record->getPrevious(),
            ];
        }

        // Translate Traversable object into simple array
        if ($this->convertTraversable && $record instanceof Traversable) {
            $record = (array)$record;
        }

        // Traverse over array
        if (is_array($record)) {
            $clean = [];
            foreach ($record as $key => $value) {
                // Remove elements with null value
                if ($this->removeNull && is_null($value)) {
                    unset($clean[$key]);
                    continue;
                }
                $clean[$key] = $this->cleanup($value);
            }

            return $clean;
        }

        // Strip unwanted strings from simple scalar values
        if ($this->stripTags && is_scalar($record)) {
            return str_replace($this->stripTags, '', $record);
        }

        // Return original value if not scalar or array
        return $record;
    }

    /**
     * Set flag.
     *
     * @param bool $convertThrowable
     *
     * @return CleanupProcessor
     */
    public function setConvertThrowable(bool $convertThrowable)
    {
        $this->convertThrowable = $convertThrowable;

        return $this;
    }

    /**
     * Set flag.
     *
     * @param bool $convertTraversable
     *
     * @return CleanupProcessor
     */
    public function setConvertTraversable(bool $convertTraversable)
    {
        $this->convertTraversable = $convertTraversable;

        return $this;
    }

    /**
     * Set flag.
     *
     * @param bool $removeNull
     *
     * @return CleanupProcessor
     */
    public function setRemoveNull(bool $removeNull)
    {
        $this->removeNull = $removeNull;

        return $this;
    }

    /**
     * Set flag.
     *
     * @param array|string $stripTags
     *
     * @return CleanupProcessor
     */
    public function setStripTags($stripTags)
    {
        $this->stripTags = $stripTags;

        return $this;
    }
}
