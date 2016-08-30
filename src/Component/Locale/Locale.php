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
 * @package Ansas\Component\Locale
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Locale
{
    /**
     * @var string locale
     */
    protected $locale;

    /**
     * Locale constructor.
     *
     * @param $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = Locale::sanitizeLocale($locale);
    }

    /**
     * Return String representation of object.
     *
     * @return string Sanitized locale.
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
     * @return Locale
     */
    public static function create($locale)
    {
        return new static($locale);
    }

    /**
     * Get country code.
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
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getCountryName($inLocale = null)
    {
        $inLocale = $inLocale ? Locale::sanitizeLocale($inLocale) : $this->locale;

        return \Locale::getDisplayRegion($this->locale, $inLocale);
    }

    /**
     * Get language code.
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
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getLanguageName($inLocale = null)
    {
        $inLocale = $inLocale ? Locale::sanitizeLocale($inLocale) : $this->locale;

        return \Locale::getDisplayLanguage($this->locale, $inLocale);
    }

    /**
     * Get locale code.
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
     * @param Locale|string $inLocale [optional] Set the locale to display value in.
     *
     * @return string
     */
    public function getLocaleName($inLocale = null)
    {
        $inLocale = $inLocale ? Locale::sanitizeLocale($inLocale) : $this->locale;

        return \Locale::getDisplayName($this->locale, $inLocale);
    }

    /**
     * Sanitize given locale.
     *
     * @param string|Locale $locale
     *
     * @return string
     * @throws Exception
     */
    public static function sanitizeLocale($locale)
    {
        if ($locale instanceof Locale) {
            return $locale->getLocale();
        }

        if (!preg_match('/^(?<language>[a-z]{2})[^a-z](?<country>[a-z]{2})$/ui', (string) $locale, $found)) {
            throw new Exception("Cannot determine locale");
        }

        $found['language'] = Text::toCase($found['language'], Text::LOWER);
        $found['country']  = Text::toCase($found['country'], Text::UPPER);

        return sprintf("%s_%s", $found['language'], $found['country']);
    }
}
