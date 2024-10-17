<?php

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

class Text
{
    const NONE        = 'none';
    const LOWER       = 'lower';
    const LOWER_FIRST = 'lowerFirst';
    const UPPER       = 'upper';
    const UPPER_FIRST = 'upperFirst';
    const UPPER_WORDS = 'upperWords';

    /**
     * @var string UTF-8 ByteOrderMark sequence
     */
    protected static string $bom = "\xEF\xBB\xBF";

    public static function firstChar(string $string, int $first = 1, int $from = 0): string
    {
        return mb_substr($string, $from, $first);
    }

    public static function firstLine(string $string): string
    {
        return preg_replace('/\r?\n.+$/us', '', $string);
    }

    public static function fixUtf8(string $string): string
    {
        $before = substr_count($string, '?');
        $fixed  = Encoding::fixUTF8($string);
        $after  = substr_count($fixed, '?');

        // Only use fixed verion if no invalid chars were added
        // Note: invalid chars are converted to question marks (?)
        return $before == $after ? $fixed : $string;
    }

    /**
     * Replace first occurrence to $search in $text by $replace.
     */
    public static function hasBom(?string $text, bool $anywhere = false): bool
    {
        if ($anywhere) {
            return !!preg_match("/" . static::$bom . "/u", $text);
        }

        return substr($text, 0, 3) === static::$bom;
    }

    /**
     * Check if string is complete lower case.
     */
    public static function isLower(?string $string): bool
    {
        return ctype_lower($string);
    }

    public static function isUpper(?string $string): bool
    {
        return ctype_upper($string);
    }

    public static function isUtf8(?string $string): bool
    {
        return mb_detect_encoding($string, 'UTF-8', true) !== false;
    }

    /**
     * Get max bytes needed per char.
     */
    public static function maxCharWidth(?string $string): int
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
     */
    public static function removeBom(?string $text, bool $anywhere = false): string
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
     */
    public static function removePrefix(string $prefix, string $text, bool $ignoreCase = false): string
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

    public static function replace(
        string|array $search,
        string|array $replace,
        string|array $text,
        bool         $partial = false
    ): null|string|array {
        return preg_replace(
            array_map(fn($v) => static::toRegex($v, 'ui', !$partial), (array) $search),
            $replace,
            $text
        );
    }

    /**
     * Replace first occurrence to $search in $text by $replace.
     */
    public static function replaceFirst(string $search, string $replace, string $text): string
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

    public static function space(mixed $text, int $step, bool $reverse = false, string $char = ' '): string
    {
        $text = trim($text, $char);

        if ($reverse) {
            $text = strrev(chunk_split(strrev($text), $step, $char));
        } else {
            $text = chunk_split($text, $step, $char);
        }

        return trim($text, $char);
    }

    /**
     * Remove 4-byte-chars (like emojis) in text.
     */
    public static function strip4ByteChars(string $text, string $replaceWith = ''): string
    {
        return preg_replace('/[\xF0-\xF7].../s', $replaceWith, $text);
    }

    /**
     * The email must at least contain an @ and have a second-level domain.
     */
    public static function stripEmails(string $text, string $replaceWith = ''): string
    {
        return preg_replace('/[^@\s>]+@[^@\s<>]+\.[^@\s<]+/u', $replaceWith, $text);
    }

    /**
     * This method can remove these types:
     * - <code>http://test.de</code> (with every protocol)
     * - <code>//test.de</code> (without protocol)
     * - <code>www.test.de</code> (with www subdomain)
     * - <code>www.test.de/test/test.htm?test=1&test2=2</code> (with path, file and param suffix)
     * - <code>test.de/sub</code> (with path)
     */
    public static function stripLinks(string $text, string $replaceWith = '', ?array $topLevelDomains = []): string
    {
        $text = preg_replace('/(?:(?:[^\s\:>]+:)?\/\/|www\.)[^\s\.]+\.\w+[^\s<]+/u', $replaceWith, $text);
        $text = preg_replace('/[^\s\.>]+\.[a-z]{2,}\/[^\s<]+/u', $replaceWith, $text);

        if (null !== $topLevelDomains) {
            $list = $topLevelDomains ? join('|', $topLevelDomains) : '[a-z]{2,5}';
            $text = preg_replace('/\b[^\s\.>]+\.(?:' . $list . ')\b/ui', $replaceWith, $text);
        }

        return $text;
    }

    /**
     * Remove phone numbers in text (ALPHA!)
     *
     * This method can remove these types:
     * - <code>0541 123456</code>
     * - <code>+49 (0) 541 / 123 - 456</code>
     *
     * Notes:
     * - Phone number must begin with + or 0
     * - This method will also remove UCP or EAN starting with 0
     */
    public static function stripPhones(string $text, string $replaceWith = ''): string
    {
        return preg_replace('/(?:\+\s?|(?<!\d)0+)[1-9][\d\s\(\)\/\-]+\d{3,}[\d\s\(\)\/\-]+\d/u', $replaceWith, $text);
    }

    /**
     * Remove prices in text
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
     * Remove social hints in text
     *
     * This method can remove these types:
     * - <code>@test</code> (twitter)
     * - <code>facebook.com/test</code> (facebook)
     */
    public static function stripSocials(string $text, string $replaceWith = ''): string
    {
        $text = preg_replace('/(?<=\s|^|>)@[^\s<]+/u', $replaceWith, $text);

        return preg_replace('/(?:[^\s>]+\.)?facebook.com\/[^\s<]+/u', $replaceWith, $text);
    }

    /**
     * Convert json string to array
     *
     * @throws InvalidArgumentException
     */
    public static function toArray(string $string): array
    {
        return self::toObject($string, true);
    }

    /**
     * Convert string into bool value
     *
     * @throws InvalidArgumentException
     */
    public static function toBool(mixed $string): bool
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
     * Convert case of a string
     *
     * @throws InvalidArgumentException
     */
    public static function toCase(?string $string, string $case): string
    {
        if (is_null($string)) {
            return '';
        }

        return match ($case) {
            self::LOWER       => mb_strtolower($string),
            self::LOWER_FIRST => mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1),
            self::UPPER       => mb_strtoupper($string),
            self::UPPER_FIRST => mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1),
            self::UPPER_WORDS => mb_convert_case($string, MB_CASE_TITLE),
            self::NONE        => $string,
            default           => throw new InvalidArgumentException("Cannot set case {$case}")
        };
    }

    /**
     * Compare $v1 and $v2 and calculate factor
     */
    public static function toFactor(string $v1, string $v2, bool $ignoreCase = false): int
    {
        $function = $ignoreCase ? 'strcasecmp' : 'strcmp';
        $result   = $function($v1, $v2);

        return $result > 0 ? 1 : ($result < 0 ? -1 : 0);
    }

    /**
     * Convert to float (remove nun numeric chars).
     */
    public static function toFloat(mixed $string): float
    {
        $string = (string) $string;

        if (mb_strlen($string)) {
            // Remove all not allowed chars
            $string = preg_replace('/[^0-9,\-\.\+]/', '', $string);

            // Sanitize sign (+/-) at end of number
            $string = preg_replace('/^(.*)(\-|\+)$/', '$2$1', $string);

            // convert american or european styled numbers
            $string = str_replace(",",".", $string);
            $string = preg_replace('/\.(?=.*\.)/', '', $string);
        }

        return (float) $string;
    }

    /**
     * Convert json string to object or array
     *
     * @throws InvalidArgumentException
     */
    public static function toObject(?string $string, bool $assoc = false): array|object
    {
        $obj = json_decode($string, $assoc);
        if (is_null($obj) || is_scalar($obj)) {
            throw new InvalidArgumentException("Invalid JSON string");
        }

        return $obj;
    }

    public static function toLower(?string $string): string
    {
        return self::toCase($string, self::LOWER);
    }

    public static function toLowerFirst(?string $string): string
    {
        return self::toCase($string, self::LOWER_FIRST);
    }

    public static function toRegex(string $string, string $modifiers = 'u', bool $exact = false): string
    {
        // Check if string is already a regular expression
        if (preg_match('/^\/.+\/[a-z]*$/i', $string)) {
            return $string;
        }

        $string = preg_quote($string, '/');
        if ($exact) {
            $string = sprintf('^%s$', $string);
        }

        // Quote special regex chars, add delimiters and modifiers
        return sprintf('/%s/%s', $string, $modifiers);
    }

    /**
     * Convert to [trimmed] [single line] string without multiple whitespaces
     */
    public static function toSingleWhitespace(?string $string, bool $trim = true, bool $singleLine = true): string
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
     * Convert e.g. 8M to size in bytes
     */
    public static function toSizeInByte(string $string, string $system = 'metric'): int
    {
        $mod = ($system === 'binary') ? 1024 : 1000;

        $size = self::toFloat($string);
        $unit = substr(strpbrk(strtolower($string), 'kmgtpezy'), 0, 1);
        if ($unit) {
            $size *= pow($mod, stripos('bkmgtpezy', $unit));
        }

        return (int) round($size);
    }

    public static function toNormalized(?string $string, string $case = self::LOWER): string
    {
        if (is_null($string)) {
            return '';
        }

        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^0-9a-zA-Z]/', '', $string);

        return self::toCase($string, $case);
    }

    public static function toSlug(string $string): string
    {
        $string = preg_replace('/[^\p{L}\d]+/u', '-', $string);
        $string = iconv('UTF-8', 'US-ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^-\w]+/', '', $string);
        $string = trim($string, '-');
        $string = preg_replace('/-+/', '-', $string);

        return strtolower($string);
    }

    public static function toUpper(?string $string): string
    {
        return self::toCase($string, self::UPPER);
    }

    public static function toUpperFirst(?string $string): string
    {
        return self::toCase($string, self::UPPER_FIRST);
    }

    public static function toUpperWords(?string $string): string
    {
        return self::toCase($string, self::UPPER_WORDS);
    }

    public static function toUtf8(?string $string): string
    {
        return self::isUtf8($string) ? $string : utf8_encode($string);
    }

    /**
     * @throws ContextException
     */
    public static function toXml(string $string, bool $isFile = false): SimpleXMLElement
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

    public static function trim(string $string): string
    {
        return trim($string);
    }

    /**
     * Preserves words and adds suffix "..." by default
     */
    public static function truncate(string $string, int $limit, bool $preserve = true, string $suffix = '...'): string
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
