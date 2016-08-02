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
use Traversable;

/**
 * CleanupProcessor
 *
 * Removes bloating data from log provided via constructor parameter
 * Note: Always removes keys with null values
 *
 * @author Ansas Meyer <mail@ansas-meyer.de>
 */
class CleanupProcessor
{
    protected $strip;

    /**
     * @param mixed $strip
     */
    public function __construct($strip = [])
    {
        if (!is_scalar($strip) && !is_array($strip)) {
            throw new Exception("Strip must be scalar or array of scalar.");
        }
        $this->strip = $strip;
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
     * @param  array $record
     * @return array
     */
    protected function cleanup($record)
    {
        if (is_array($record) || $record instanceof Traversable) {
            $clean = [];
            foreach ($record as $key => $value) {
                if (is_null($value)) {
                    unset($clean[$key]);
                    continue;
                }
                $clean[$key] = $this->cleanup($value);
            }
            return $clean;
        }


        if ($this->strip && is_scalar($record)) {
            return str_replace($this->strip, '', $record);
        }

        return $record;
    }
}
