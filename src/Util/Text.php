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
 * Class Text
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Text
{
    /**
     * Set case to upper
     */
    const UPPER = 'upper';
    /**
     * Set case to lower
     */
    const LOWER = 'lower';
    /**
     * Use case "as is"
     */
    const NONE = 'none';

    /**
     * Convert case of a string.
     *
     * @param string $string
     * @param string $case
     *
     * @return string
     * @throws Exception
     */
    public static function toCase(string $string, $case)
    {
        switch ($case) {
            case self::UPPER:
                $string = strtoupper($string);
                break;
            case self::LOWER:
                $string = strtolower($string);
                break;
            case self::NONE:
                break;
            default:
                throw new Exception(sprintf("Cannot set case %s", $case));
        }

        return $string;
    }

    /**
     * Convert string into regex.
     *
     * @param string $string    String to convert into regex.
     * @param string $modifiers [optional] Modifiers to add to regex.
     *
     * @return string
     */
    public static function toRegex(string $string, $modifiers = 'u')
    {
        // Check if string is already a regular expression
        if (substr($string, 0, 1) == '/') {
            return $string;
        }

        // Quote special regex chars, add delimiters and modifiers
        return '/' . preg_quote($string) . '/' . $modifiers;
    }
}
