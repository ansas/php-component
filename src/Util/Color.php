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

/**
 * Class Color
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Color
{
    /**
     * Convert HEX color into RGB (or RGBA) value and return parts as array.
     *
     * @param string $hex
     * @param float  $alpha [optional]
     *
     * @return array
     */
    public static function hex2rgb($hex, $alpha = null)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 6) {
            $rgb['r'] = hexdec(substr($hex, 0, 2));
            $rgb['g'] = hexdec(substr($hex, 2, 2));
            $rgb['b'] = hexdec(substr($hex, 4, 2));
        } elseif (strlen($hex) == 3) {
            $rgb['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $rgb['r'] = '0';
            $rgb['g'] = '0';
            $rgb['b'] = '0';
        }

        if (null !== $alpha) {
            $rgb['a'] = $alpha;
        }

        return $rgb;
    }
}
