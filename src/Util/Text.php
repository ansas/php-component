<?php

/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpMissingParamTypeInspection */
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
     * @var string UTF-8 ByteOrderMark sequence
     */
    protected static $bom = "\xEF\xBB\xBF";

    /**
     * @param string $string
     * @param int    $first [optional]
     * @param int    $from  [optional]
     *
     * @return string
     */
    public static function firstChar($string, $first = 1, $from = 0): string
    {
        return mb_substr($string, $from, $first);
    }

    /**
     * Replace first occurrence to $search in $text by $replace.
     *
     * @param string $text
     * @param bool   $anywhere [optional]
     *
     * @return bool
     */
    public static function hasBom($text, $anywhere = false): bool
    {
        if ($anywhere) {
            return !!preg_match("/" . static::$bom . "/u", $text);
        }

        return substr($text, 0, 3) === static::$bom;
    }

    /**
     * Check if string is complete lower case.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isLower($string): bool
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
    public static function isUpper($string): bool
    {
        return ctype_upper($string);
    }

    /**
     * Check if string is UTF-8.
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isUtf8($string): bool
    {
        return mb_detect_encoding($string, 'UTF-8', true) !== false;
    }

    /**
     * Get max bytes needed per char.
     *
     * @param string $string
     *
     * @return int
     */
    public static function maxCharWidth($string): int
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
     * @param string $text
     * @param bool   $anywhere [optional]
     *
     * @return string
     */
    public static function removeBom($text, $anywhere = false): string
    {
        if ($anywhere) {
            return preg_replace("/" . static::$bom . "/u", '', $text);
        }

        if (substr($text, 0, 3) === static::$bom) {
            return substr($text, 3);
        }

        return $text;
    }

    /**
     * Replace first occurrence to $search in $text by $replace.
     *
     * @param string $prefix
     * @param string $text
     * @param bool   $ignoreCase [optional]
     *
     * @return string
     */
    public static function removePrefix($prefix, $text, $ignoreCase = false): string
    {
        $length = mb_strlen($prefix);
        if ($length) {
            $function = $ignoreCase ? 'mb_stripos' : 'mb_strpos';
            if (0 === $function($text, $prefix)) {
                $text = mb_substr($text, $length);
            }
        }

        return $text;
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
    public static function replaceFirst($search, $replace, $text): string
    {
        $length = strlen($search);
        if ($length) {
            $pos = strpos($text, $search);
            if ($pos !== false) {
                $text = substr_replace($text, $replace, $pos, $length);
            }
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
    public static function stripEmails($text, $replaceWith = ''): string
    {
        return preg_replace('/[^@\s>]+@[^@\s<>]+\.[^@\s<]+/u', $replaceWith, $text);
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
    public static function stripLinks($text, $replaceWith = '', $topLevelDomains = []): string
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
    public static function stripPhones($text, $replaceWith = ''): string
    {
        return preg_replace('/(?:\+\s?|(?<!\d)0+)[1-9][\d\s\(\)\/\-]+\d{3,}[\d\s\(\)\/\-]+\d/u', $replaceWith, $text);
    }

    /**
     * Remove prices in text.
     *
     * This method can remove these types:
     * - <code>USD 1.23</code>
     * - <code>USD1,234.56</code>
     * - <code>$ 1.23</code>
     * - <code>$12.3</code>
     * - <code>1.230,00 EUR</code>
     * - <code>12,3EUR</code>
     * - <code>1,23 €</code>
     * - <code>12,3€</code>
     */
    public static function stripPrices(string $text, string $replaceWith = '', array $currencies = ['€', 'EUR', '$', 'USD']): string
    {
        // Make sure to quote special chars (like symbol $) correctly
        $currencies = str_replace(' ', '|', preg_quote(implode(' ', $currencies)));

        return preg_replace("/((?:{$currencies})\s*[\d,.]+\d|\d[\d,.]+\s*(?:{$currencies}))/ui", $replaceWith, $text);
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
    public static function stripSocials($text, $replaceWith = ''): string
    {
        $text = preg_replace('/(?<=\s|^|>)@[^\s<]+/u', $replaceWith, $text);

        return preg_replace('/(?:[^\s>]+\.)?facebook.com\/[^\s<]+/u', $replaceWith, $text);
    }

    /**
     * Convert json string to array.
     *
     * @throws InvalidArgumentException
     */
    public static function toArray(string $string): array
    {
        return self::toObject($string, true);
    }

    /**
     * Convert string into bool value.
     *
     * @param mixed $string
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function toBool($string): bool
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
     * @param string|null $string
     * @param string      $case
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function toCase(?string $string, string $case): string
    {
        if (is_null($string)) {
            $string = '';
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
     * Compare $v1 and $v2 and calculate factor
     *
     * @param string $v1
     * @param string $v2
     * @param bool   $ignoreCase [optional]
     *
     * @return int
     */
    public static function toFactor($v1, $v2, $ignoreCase = false): int
    {
        $function = $ignoreCase ? 'strcasecmp' : 'strcmp';
        $result   = $function($v1, $v2);

        return $result > 0 ? 1 : ($result < 0 ? -1 : 0);
    }

    /**
     * Convert to float (remove nun numeric chars).
     */
    public static function toFloat($string): float
    {
        $string = (string) $string;

        if (mb_strlen($string)) {
            // Remove all not allowed chars
            $string = preg_replace('/[^0-9,\-\.\+]/', '', $string);

            // Sanitize sign (+/-) at end of number
            $string = preg_replace('/^(.*)(\-|\+)$/', '$2$1', $string);

            // Sanitize comma (,) in price
            if (mb_strpos($string, ',') !== false) {
                if (preg_match('/,\d+\./', $string)) {
                    $string = str_replace(',', '', $string);
                } else {
                    $string = str_replace('.', '', $string);
                    $string = str_replace(',', '.', $string);
                }
            }
        }

        return (float) $string;
    }

    /**
     * Convert json string to object or array.
     *
     * @param string $string
     * @param bool   $assoc [optional]
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function toObject($string, $assoc = false)
    {
        $obj = json_decode($string, $assoc);
        if (null === $obj) {
            throw new InvalidArgumentException("Invalid JSON string");
        }

        return $obj;
    }

    /**
     * Convert to lower string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toLower($string): string
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
    public static function toRegex($string, $modifiers = 'u'): string
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
    public static function toSingleWhitespace($string, $trim = true, $singleLine = true): string
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
    public static function toSizeInByte($string, $system = 'metric'): int
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
     * @param string|null $string
     * @param string      $case [optional]
     *
     * @return string
     */
    public static function toNormalized(?string $string, $case = self::LOWER): string
    {
        if (is_null($string)) {
            return '';
        }

        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^0-9a-zA-Z]/', '', $string);

        return self::toCase($string, $case);
    }

    /**
     * Convert into slug.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toSlug($string): string
    {
        $string = preg_replace('/[^\p{L}\d]+/u', '-', $string);
        $string = iconv('UTF-8', 'US-ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^-\w]+/', '', $string);
        $string = trim($string, '-');
        $string = preg_replace('/-+/', '-', $string);

        return strtolower($string);
    }

    /**
     * Convert to upper string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUpper($string): string
    {
        return self::toCase($string, self::UPPER);
    }

    /**
     * Convert string to UFT-8.
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUtf8($string): string
    {
        return self::isUtf8($string) ? $string : utf8_encode($string);
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
    public static function toXml($string, $isFile = false): SimpleXMLElement
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
    public static function trim($string): string
    {
        return trim($string);
    }

    /**
     * Truncate text.
     *
     * Preserves words and adds suffix "..." by default
     *
     * @param string $string
     * @param int    $limit
     * @param bool   $preserve [optional] Preserve words if possible
     * @param string $suffix   [optional]
     *
     * @return string
     */
    public static function truncate($string, int $limit, $preserve = true, $suffix = '...'): string
    {
        if (mb_strlen($string) > $limit) {
            $breakpoint = $limit - mb_strlen($suffix);

            if ($preserve) {
                $breakpoint = mb_strrpos(mb_substr($string, 0, $breakpoint + 1), ' ') ?: $breakpoint;
            }

            $string = rtrim(mb_substr($string, 0, $breakpoint)) . $suffix;
        }

        return $string;
    }
}
