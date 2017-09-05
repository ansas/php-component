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
     * Set case to upper (first letter)
     */
    const UPPER_FIRST = 'upperFirst';

    /**
     * Set case to upper (first letter of every word)
     */
    const UPPER_WORDS = 'upperWords';

    /**
     * Set case to lower
     */
    const LOWER = 'lower';

    /**
     * Use case "as is"
     */
    const NONE = 'none';

    /**
     * Check if string is complete lower case.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isLower($string)
    {
        return ctype_lower($string);
    }

    /**
     * Check if string is complete upper case.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isUpper($string)
    {
        return ctype_upper($string);
    }

    /**
     * Remove links in text.
     *
     * This method can remove these types:
     * - <code>http://test.de</code> (with every protocol)
     * - <code>//test.de</code> (without protocol)
     * - <code>www.test.de</code> (with www subdomain)
     * - <code>www.test.de/test/test.htm?test=1&test2=2</code> (with path, file and param suffix)
     *
     * @param string $text
     * @param string $replaceWith [optional]
     *
     * @return string
     */
    public static function stripLinks($text, $replaceWith = '')
    {
        $text = preg_replace('/(?:(?:\S+:)?\/\/|www\.)[^\s\.]+\.\w+[^\s<]+/u', $replaceWith, $text);

        return $text;
    }

    /**
     * Convert string into bool value.
     *
     * @param string $string
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function toBool($string)
    {
        if (!is_scalar($string)) {
            throw new InvalidArgumentException("Value must be scalar");
        }

        if (!is_string($string)) {
            return (bool) $string;
        }

        return !in_array(strtolower($string), ['false', 'off', '-', 'no', 'n', '0', '']);
    }

    /**
     * Convert case of a string.
     *
     * @param string $string
     * @param string $case
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function toCase($string, $case)
    {
        switch ($case) {
            case self::UPPER:
                $string = mb_strtoupper($string);
                break;
            case self::LOWER:
                $string = mb_strtolower($string);
                break;
            case self::UPPER_FIRST:
                $string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
                break;
            case self::UPPER_WORDS:
                $string = mb_convert_case($string, MB_CASE_TITLE);
                break;
            case self::NONE:
                break;
            default:
                throw new InvalidArgumentException("Cannot set case {$case}");
        }

        return $string;
    }

    /**
     * Convert to lower string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toLower($string)
    {
        return self::toCase($string, self::LOWER);
    }

    /**
     * Convert string into regex.
     *
     * @param string $string    String to convert into regex.
     * @param string $modifiers [optional] Modifiers to add to regex.
     *
     * @return string
     */
    public static function toRegex($string, $modifiers = 'u')
    {
        // Check if string is already a regular expression
        if (substr($string, 0, 1) == '/') {
            return $string;
        }

        // Quote special regex chars, add delimiters and modifiers
        return '/' . preg_quote($string) . '/' . $modifiers;
    }

    /**
     * Convert to upper string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUpper($string)
    {
        return self::toCase($string, self::UPPER);
    }
}
