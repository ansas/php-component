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

/**
 * Class Locales
 *
 * @package Ansas\Component\Locale
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Locales
{
    /**
     * @var Locale[] List of locales
     */
    protected $locales;

    /**
     * @var Locale Default (fallback) locale
     */
    protected $default;

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

        if (!isset($this->locales[$locale])) {
            $locale                              = new Locale($locale);
            $this->locales[$locale->getLocale()] = $locale;
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
     * @return Locales
     */
    public static function create($locales = [])
    {
        return new static($locales);
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
        return $this->locales;
    }

    /**
     * Get default (fallback) locale.
     *
     * @return Locale|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get locale with specified name.
     *
     * @param string|Locale $locale
     *
     * @return Locale|null
     */
    public function getLocale($locale)
    {
        // Get sanitized locale
        $locale = Locale::sanitizeLocale($locale);

        return $this->locales[$locale] ?? null;
    }

    /**
     * Set the current active locale.
     *
     * @param string|Locale $locale
     *
     * @return $this
     */
    public function setActive($locale)
    {
        // Make sure locale is available (set it on list if not set yet)
        $this->addLocale($locale);

        // Mark as current locale
        $this->active = $this->getLocale($locale);

        // Set as global default for other classes
        \Locale::setDefault($this->active->getLocale());

        return $this;
    }

    /**
     * Set the default locale.
     *
     * @param string|Locale $locale
     *
     * @return $this
     */
    public function setDefault($locale)
    {
        // Make sure locale is available (set it on list if not set yet)
        $this->addLocale($locale);

        // Mark as default locale
        $this->default = $this->getLocale($locale);

        // Mark as current locale (if none set yet)
        if (!$this->active) {
            $this->setActive($this->default);
        }

        return $this;
    }
}
