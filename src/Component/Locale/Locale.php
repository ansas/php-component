<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Locale;

use Ansas\Util\Text;
use Exception;

/**
 * Class Locale
 *
 * The easy way of translating country and language names. Instead of building custom language and country mapping let
 * PHP do the work for you. This wrapper class makes locale handling even easier.
 *
 * @package Ansas\Component\Locale
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Locale
{
    /**
     * Regular expression for finding locales (not strict "de_DE", also supports e. g. "de-de")
     */
    const REGEX_LOCALE = '(?<locale>(?<language>[a-z]{2})[^a-z](?<country>[a-z]{2}))';

    /**
     * @var string The locale
     */
    protected $locale;

    /**
     * Locale constructor.
     *
     * @param string $locale
     *
     * @throws Exception
     */
    public function __construct($locale)
    {
        $this->locale = Locale::sanitizeLocale($locale);

        if (!$this->locale) {
            throw new Exception("Locale string invalid.");
        }
    }

    /**
     * Return String representation of object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLocale();
    }

    /**
     * Create new instance via static method.
     *
     * @param $locale
     *
     * @return static
     */
    public static function create($locale)
    {
        return new static($locale);
    }

    /**
     * Sanitize given country from locale.
     *
     * Must be a simple locale without code set, currency or similar (e. g. "de_DE" or "de-de" or "DE").
     *
     * @param string|Locale $locale
     * @param string|Locale $default [optional] The default locale to return if $locale is invalid
     *
     * @return null|string
     */
    public static function sanitizeCountry($locale, $default = null)
    {
        if ($locale instanceof Locale) {
            return $locale->getCountry();
        }

        if ($default) {
            $default = Locale::sanitizeCountry($default, null);
        }

        $locale = (string) $locale;
        $locale = trim($locale);

        if (!$locale || !preg_match("/(?<country>[a-z]{2})$/ui", $locale, $found)) {
            return $default;
        }

        return Text::toUpper($found['country']);
    }

    /**
     * Sanitize given language from locale.
     *
     * Must be a simple locale without code set, currency or similar (e. g. "de_DE" or "de-de" or "de").
     *
     * @param string|Locale $locale
     * @param string|Locale $default [optional] The default locale to return if $locale is invalid
     *
     * @return null|string
     */
    public static function sanitizeLanguage($locale, $default = null)
    {
        if ($locale instanceof Locale) {
            return $locale->getLanguage();
        }

        if ($default) {
            $default = Locale::sanitizeLanguage($default, null);
        }

        $locale = (string) $locale;
        $locale = trim($locale);

        if (!$locale || !preg_match("/^(?<language>[a-z]{2})/ui", $locale, $found)) {
            return $default;
        }

        return Text::toLower($found['language']);
    }

    /**
     * Sanitize given locale.
     *
     * Must be a simple locale without code set, currency or similar (e. g. "de_DE" or "de-de").
     *
     * @param string|Locale $locale
     * @param string|Locale $default [optional] The default locale to return if $locale is invalid
     *
     * @return null|string
     */
    public static function sanitizeLocale($locale, $default = null)
    {
        if ($locale instanceof Locale) {
            return $locale->getLocale();
        }

        if ($default) {
            $default = Locale::sanitizeLocale($default, null);
        }

        $locale = (string) $locale;
        $locale = trim($locale);

        if (!$locale || !preg_match("/^" . Locale::REGEX_LOCALE . "$/ui", $locale, $found)) {
            return $default;
        }

        $found['language'] = Text::toLower($found['language']);
        $found['country']  = Text::toUpper($found['country']);

        return sprintf("%s_%s", $found['language'], $found['country']);
    }

    /**
     * Get country code.
     *
     * If param $case is not set the result will be in UPPER case (e. g. "DE") by default.
     *
     * @param string $case [optional] Convert value to case (Text::LOWER, Text::UPPER or Text::NONE)
     *
     * @return string
     */
    public function getCountry($case = Text::NONE)
    {
        return Text::toCase(\Locale::getRegion($this->locale), $case);
    }

    /**
     * Get country name.
     *
     * If param $inLocale is not set the result will be in the local original language.
     *
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getCountryName($inLocale = null)
    {
        $inLocale = Locale::sanitizeLocale($inLocale, $this->locale);

        return \Locale::getDisplayRegion($this->locale, $inLocale);
    }

    /**
     * Get country name for specified locale or country-code.
     *
     * If param $inLocale is not set the result will be in the local original language.
     *
     * @param Locale|string $locale   Locale (or locale part "country") to get name for.
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getCountryNameFor($locale, $inLocale = null)
    {
        $locale   = '_' . Locale::sanitizeCountry($locale);
        $inLocale = Locale::sanitizeLanguage($inLocale, $this->locale);

        return \Locale::getDisplayRegion($locale, $inLocale);
    }

    /**
     * Get language code.
     *
     * If param $case is not set the result will be in LOWER case (e. g. "de") by default.
     *
     * @param string $case [optional] Convert value to case (Text::LOWER, Text::UPPER or Text::NONE)
     *
     * @return string
     */
    public function getLanguage($case = Text::NONE)
    {
        return Text::toCase(\Locale::getPrimaryLanguage($this->locale), $case);
    }

    /**
     * Get language name.
     *
     * If param $inLocale is not set the result will be in the local original language.
     *
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getLanguageName($inLocale = null)
    {
        $inLocale = Locale::sanitizeLocale($inLocale, $this->locale);

        return \Locale::getDisplayLanguage($this->locale, $inLocale);
    }

    /**
     * Get language name for specified locale or language-code.
     *
     * If param $inLocale is not set the result will be in the local original language.
     *
     * @param Locale|string $locale   Locale (or locale part "language") to get name for.
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getLanguageNameFor($locale, $inLocale = null)
    {
        $locale   = Locale::sanitizeLanguage($locale) . '_';
        $inLocale = Locale::sanitizeLanguage($inLocale, $this->locale);

        return \Locale::getDisplayLanguage($locale, $inLocale);
    }

    /**
     * Get locale code.
     *
     * The result will always be in the sanitized form (e. g. "de_DE").
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get locale name.
     *
     * If param $inLocale is not set the result will be in the local original language.
     *
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getLocaleName($inLocale = null)
    {
        $inLocale = Locale::sanitizeLocale($inLocale, $this->locale);

        return \Locale::getDisplayName($this->locale, $inLocale);
    }
}
