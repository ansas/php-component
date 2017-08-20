<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Exception;

use Exception;

/**
 * Trait CreateFromExceptionTrait
 *
 * Create new Exception from another default exception class by static method
 *
 * @package Ansas\Component\Exception
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
trait CreateFromExceptionTrait
{
    /**
     * Create new instance.
     *
     * @return static
     */
    public static function createFromException(Exception $e)
    {
        return new static($e->getMessage(), $e->getCode(), $e->getPrevious());
    }
}
