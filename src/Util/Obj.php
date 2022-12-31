<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

class Obj
{
    public static function toArray(array|object $object): array
    {
        return json_decode(json_encode((array) $object), true);
    }

    public static function toString(object $object): string
    {
        return (string) $object;
    }
}
