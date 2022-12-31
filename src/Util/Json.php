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

use InvalidArgumentException;

class Json
{
    public static function onlyChanged(string $old, string $new): ?array
    {
        // Check if provided values are valid JSON
        try {
            $old = static::toArray($old);
            $new = static::toArray($new);
        } catch (\Exception) {
            return null;
        }

        [$old, $new] = Arr::onlyChanged($old, $new);

        // Convert clean old/new lists back to string
        $old = static::toString($old);
        $new = static::toString($new);

        return [$old, $new];
    }

    public static function toArray(string $json): array
    {
        return Text::toArray($json);
    }

    public static function toObject(string $json): object
    {
        return Text::toObject($json);
    }

    public static function toString(array|object $json): string
    {
        $string = json_encode($json);
        if (false === $string) {
            throw new InvalidArgumentException("Invalid JSON data");
        }

        return $string;
    }
}
