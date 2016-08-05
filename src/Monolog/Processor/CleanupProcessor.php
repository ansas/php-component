<?php declare(strict_types=1);

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Ansas\Monolog\Processor;

use Exception;
use Throwable;
use Traversable;

/**
 * Class CleanupProcessor
 * @package Ansas\Monolog\Processor
 * @author Ansas Meyer <mail@ansas-meyer.de>
 *
 * Removes bloating data from log provided via constructor parameter
 * Note: Always removes keys with null values
 */
class CleanupProcessor
{
    /**
     * @var array|string
     */
    protected $stripTags;

    /**
     * @var bool
     */
    protected $removeNull = true;

    /**
     * @var bool
     */
    protected $convertThrowable = true;

    /**
     * @var bool
     */
    protected $convertTraversable = true;

    /**
     * CleanupProcessor constructor.
     * @param array $strip
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
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        return $this->cleanup($record);
    }

    /**
     * @param  mixed $record
     * @return mixed
     */
    protected function cleanup($record)
    {
        // Translate Throwable (Exception || Error) object into simple array
        if ($this->convertThrowable && $record instanceof Throwable) {
            $record = [
                'message' => $record->getMessage(),
                'line' => $record->getLine(),
                'file' => $record->getFile(),
                'class' => get_class($record),
                'code' => $record->getCode(),
                'trace' => $record->getTrace(),
                'previous' => $record->getPrevious(),
            ];
        }

        // Translate Traversable object into simple array
        if ($this->convertTraversable && $record instanceof Traversable) {
            $record = (array) $record;
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
     * @param array|string $stripTags
     * @return CleanupProcessor
     */
    public function setStripTags($stripTags)
    {
        $this->stripTags = $stripTags;
        return $this;
    }

    /**
     * @param boolean $removeNull
     * @return CleanupProcessor
     */
    public function setRemoveNull(bool $removeNull)
    {
        $this->removeNull = $removeNull;
        return $this;
    }

    /**
     * @param boolean $convertThrowable
     * @return CleanupProcessor
     */
    public function setConvertThrowable(bool $convertThrowable)
    {
        $this->convertThrowable = $convertThrowable;
        return $this;
    }

    /**
     * @param boolean $convertTraversable
     * @return CleanupProcessor
     */
    public function setConvertTraversable(bool $convertTraversable)
    {
        $this->convertTraversable = $convertTraversable;
        return $this;
    }
}
