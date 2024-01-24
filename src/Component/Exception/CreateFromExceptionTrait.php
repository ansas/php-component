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

trait CreateFromExceptionTrait
{
    public static function createFromException(Exception $e): static
    {
        return new static($e->getMessage(), $e->getCode(), $e);
    }
}
