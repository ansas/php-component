<?php

/** @noinspection PhpUnused */
/** @noinspection RegExpRedundantEscape */
/** @noinspection SpellCheckingInspection */

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

use Ansas\Component\Exception\ContextException;
use InvalidArgumentException;
use SimpleXMLElement;

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
     * @param string $string
     * @param int    $first [optional]
     * @param int    $from  [optional]
     *
     * @return int
     */
    public static function firstChar($string, $first = 1, $from = 0)
    {
        return mb_substr($string, $from, $first);
    }

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
     * Get max bytes needed per char.
     *
     * @param string $string
     *
     * @return int
     */
    public static function maxCharWidth($string)
    {
        $chars = preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);

        if (!$chars) {
            return 0;
        }

        $sizes = array_map('strlen', $chars);

        return max($sizes);
    }

    /**
     * Replace first occurrence to $search in $text by $replace.
     *
     * @param string $search
     * @param string $replace
     * @param string $text
     *
     * @return string
     */
    public static function replaceFirst($search, $replace, $text)
    {
        $pos = strpos($text, $search);
        if ($pos !== false) {
            $text = substr_replace($text, $replace, $pos, strlen($search));
        }

        return $text;
    }

    /**
     * Remove emails in text.
     *
     * The email must at least contain an @ and have a second-level domain.
     *
     * @param string $text
     * @param string $replaceWith [optional]
     *
     * @return string
     */
    public static function stripEmails($text, $replaceWith = '')
    {
        $text = preg_replace('/[^@\s>]+@[^@\s<>]+\.[^@\s<]+/u', $replaceWith, $text);

        return $text;
    }

    /**
     * Remove links in text.
     *
     * This method can remove these types:
     * - <code>http://test.de</code> (with every protocol)
     * - <code>//test.de</code> (without protocol)
     * - <code>www.test.de</code> (with www subdomain)
     * - <code>www.test.de/test/test.htm?test=1&test2=2</code> (with path, file and param suffix)
     * - <code>test.de/sub</code> (with path)
     *
     * @param string $text
     * @param string $replaceWith     [optional]
     * @param array  $topLevelDomains [optional]
     *
     * @return string
     */
    public static function stripLinks($text, $replaceWith = '', $topLevelDomains = [])
    {
        $text = preg_replace('/(?:(?:[^\s\:>]+:)?\/\/|www\.)[^\s\.]+\.\w+[^\s<]+/u', $replaceWith, $text);
        $text = preg_replace('/[^\s\.>]+\.[a-z]{2,}\/[^\s<]+/u', $replaceWith, $text);

        if ($topLevelDomains) {
            $list = join('|', $topLevelDomains);
            $text = preg_replace('/\b[^\s\.>]+\.(?:' . $list . ')\b/ui', $replaceWith, $text);
        }

        return $text;
    }

    /**
     * Remove phone numbers in text. (ALPHA!)
     *
     * This method can remove these types:
     * - <code>0541 123456</code>
     * - <code>+49 (0) 541 / 123 - 456</code>
     *
     * Notes:
     * - Phone number must begin with + or 0
     * - This method will also remove UCP or EAN starting with 0
     *
     * @param string $text
     * @param string $replaceWith [optional]
     *
     * @return string
     */
    public static function stripPhones($text, $replaceWith = '')
    {
        $text = preg_replace('/(?:\+\s?|(?<!\d)0+)[1-9][\d\s\(\)\/\-]+\d{3,}[\d\s\(\)\/\-]+\d/u', $replaceWith, $text);

        return $text;
    }

    /**
     * Remove social hints in text.
     *
     * This method can remove these types:
     * - <code>@test</code> (twitter)
     * - <code>facebook.com/test</code> (facebook)
     *
     * @param string $text
     * @param string $replaceWith [optional]
     *
     * @return string
     */
    public static function stripSocials($text, $replaceWith = '')
    {
        $text = preg_replace('/(?<=\s|^|>)@[^\s<]+/u', $replaceWith, $text);
        $text = preg_replace('/(?:[^\s>]+\.)?facebook.com\/[^\s<]+/u', $replaceWith, $text);

        return $text;
    }

    /**
     * Convert string into bool value.
     *
     * @param mixed $string
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function toBool($string)
    {
        if (is_null($string)) {
            return false;
        }

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
        if (is_null($string)) {
            $string = '';
        }

        if (!is_scalar($string)) {
            throw new InvalidArgumentException("Param string must be a scalar");
        }

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
     * Convert to float (remove nun numeric chars).
     *
     * @param string $string
     *
     * @return float
     */
    public static function toFloat($string)
    {
        return (float) preg_replace('/[^0-9\.]/u', '', $string);
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
        return '/' . preg_quote($string, '/') . '/' . $modifiers;
    }

    /**
     * Convert to [trimmed] [single line] string without multiple whitespaces.
     *
     * @param string $string
     * @param bool   $trim       [optional]
     * @param bool   $singleLine [optional]
     *
     * @return string
     */
    public static function toSingleWhitespace($string, $trim = true, $singleLine = true)
    {
        if ($singleLine) {
            $string = preg_replace("/[\r\n\t]/", " ", $string);
        }

        $string = preg_replace("/ {2,}/", " ", $string);

        if ($trim) {
            $string = self::trim($string);
        }

        return $string;
    }

    /**
     * Convert e.g. 8M to size in bytes.
     *
     * @param string $string
     * @param string $system [optional] binary | metric
     *
     * @return int
     */
    public static function toSizeInByte($string, $system = 'metric')
    {
        $mod = ($system === 'binary') ? 1024 : 1000;

        $size = self::toFloat($string);
        $unit = substr(strpbrk(strtolower($string), 'kmgtpezy'), 0, 1);
        if ($unit) {
            $size *= pow($mod, stripos('bkmgtpezy', $unit));
        }

        return (int) round($size);
    }

    /**
     * Convert into slug.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toSlug($string)
    {
        $string = preg_replace('/[^\p{L}\d]+/u', '-', $string);
        $string = iconv('UTF-8', 'US-ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^-\w]+/', '', $string);
        $string = trim($string, '-');
        $string = preg_replace('/-+/', '-', $string);
        $string = strtolower($string);

        return $string;
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

    /**
     * Convert to xml.
     *
     * @param string $string
     * @param bool   $isFile [optional]
     *
     * @return SimpleXMLElement
     * @throws ContextException
     */
    public static function toXml($string, $isFile = false)
    {
        $useErrors = libxml_use_internal_errors(true);
        if ($isFile) {
            $xml = simplexml_load_file($string);
        } else {
            $xml = simplexml_load_string($string);
        }
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($useErrors);

        if (!$xml instanceof SimpleXMLElement) {
            throw new ContextException("Cannot parse XML", Obj::toArray($errors));
        }

        return $xml;
    }

    /**
     * Trim string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function trim($string)
    {
        return trim($string);
    }
}
