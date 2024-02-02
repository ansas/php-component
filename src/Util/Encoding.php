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

use ForceUTF8\Encoding as BaseEncoding;
use Override;

class Encoding extends BaseEncoding
{
    #[Override]
    protected static function utf8_decode($text, $option = self::WITHOUT_ICONV)
    {
        if ($option == static::WITHOUT_ICONV || !function_exists('iconv')) {
            return mb_convert_encoding(
                str_replace(array_keys(static::$utf8ToWin1252), array_values(static::$utf8ToWin1252), static::toUTF8($text)),
                'ISO-8859-1',
                'UTF-8'
            );
        }

        return BaseEncoding::utf8_decode($text, $option);
    }
}
