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
 * Class Locales
 *
 * Stores locales added via constructor or addLocale() / addLocales() methods. This is a simple container class with
 * several find methods to check for available locales.
 *
 * @package Ansas\Component\Locale
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Locales
{
    /**
     * @var Locale[] List of locales
     */
    protected $available;

    /**
     * @var Locale Active (current) used locale
     */
    protected $active;

    /**
     * Locales constructor.
     *
     * @param array|Locale[] $locales [optional] List of available locales.
     */
    public function __construct($locales = [])
    {
        $this->addLocales($locales);
    }

    /**
     * Add locale to available locales.
     *
     * @param string|Locale $locale
     *
     * @return $this
     */
    public function addLocale($locale)
    {
        // Get sanitized locale
        $locale = Locale::sanitizeLocale($locale);

        if (!isset($this->available[$locale])) {
            $locale = new Locale($locale);

            $this->available[$locale->getLocale()] = $locale;
        }

        return $this;
    }

    /**
     * Add locale to available locales.
     *
     * @param array|Locale[] $locales
     *
     * @return $this
     */
    public function addLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->addLocale($locale);
        }

        return $this;
    }

    /**
     * Create new instance via static method.
     *
     * @param array|Locale[] $locales [optional] List of available locales.
     *
     * @return static
     */
    public static function create($locales = [])
    {
        return new static($locales);
    }

    /**
     * Get locale with specified language (de), country (DE) or locale (de_DE).
     *
     * @param $string
     *
     * @return Locale|null
     *
     */
    public function find($string)
    {
        if (strlen($string) == 2) {
            if (Text::isUpper($string)) {
                return $this->findByCountry($string);
            }

            return $this->findByLanguage($string);
        }

        return $this->findByLocale($string);
    }

    /**
     * Get locale with specified country.
     *
     * @param $country
     *
     * @return Locale|null
     *
     */
    public function findByCountry($country)
    {
        $country = Text::toUpper($country);
        foreach ($this->getAvailable() as $locale) {
            if ($country == $locale->getCountry(Text::UPPER)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get locale with specified language.
     *
     * @param $language
     *
     * @return Locale|null
     *
     */
    public function findByLanguage($language)
    {
        $language = Text::toLower($language);
        foreach ($this->getAvailable() as $locale) {
            if ($language == $locale->getLanguage(Text::LOWER)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get locale with specified name.
     *
     * @param string|Locale $locale
     *
     * @return Locale|null
     */
    public function findByLocale($locale)
    {
        // Get sanitized locale
        $locale = Locale::sanitizeLocale($locale);

        return $this->available[$locale] ?? null;
    }

    /**
     * Get active (current) locale.
     *
     * @return Locale|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get list of all available locales.
     *
     * @return Locale[]
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * Set the current active locale incl. localization parameters.
     *
     * @param string|Locale $locale
     *
     * @return $this
     * @throws Exception
     */
    public function setActive($locale)
    {
        $active = $this->findByLocale($locale);

        if (!$active) {
            throw new Exception(sprintf("Locale '%s' not in list.", $locale));
        }

        // Mark as current locale
        $this->active = $active;

        // Set as global default for other classes
        \Locale::setDefault($active->getLocale());

        return $this;
    }
}
