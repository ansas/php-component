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

use Exception;

/**
 * Class Localization
 *
 * Extends locales container class and enables localization via gettext(). Also this class can find all supported
 * translated locales dynamically.
 *
 * @package Ansas\Component\Locale
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Localization extends Locales
{
    /**
     * Locale code set
     */
    const CODE_SET = 'UTF-8';

    /**
     * @var Locale Default (fallback) locale
     */
    protected $default;

    /**
     * @var string Locale path
     */
    protected $path;

    /**
     * @var string Locale domain
     */
    protected $domain;

    /**
     * @var array Translation map for virtual locale and domain
     */
    protected $maps;

    /**
     * Add map for a virtual locale.
     *
     * @param string $locale
     * @param array  $map
     *
     * @return $this
     */
    public function addMap($locale, $map)
    {
        // Get sanitized locale
        $locale = Locale::sanitizeLocale($locale);

        $this->maps[$locale] = $map;

        return $this;
    }

    /**
     * Add maps for virtual locales.
     *
     * @param array[] $maps
     *
     * @return $this
     */
    public function addMaps(array $maps)
    {
        foreach ($maps as $locale => $map) {
            $this->addMap($locale, $map);
        }

        return $this;
    }

    /**
     * Dynamically find all available locales.
     *
     * This class supports "virtual locales" because often not all locales are installed on the server. If you want to
     * use <code>gettext()</code> the used locale MUST be installed. This class uses the workaround via default internal
     * locale "C" and determining the locale via filename. The mapping is done automatically.
     *
     * All locales must be stored in a "locale" folder (specified via <code>setPath()</code> in this structure:
     * - <code><pathToLocale>/<locale>/LC_MESSAGES/<domain>.mo</code> or
     * - <code><pathToLocale>/C/LC_MESSAGES/<domain>.<locale>.mo</code>
     *
     * Examples:
     * - <code>de_DE/LC_MESSAGES/default.mo</code> or
     * - <code>C/LC_MESSAGES/app.en_GB.mo</code>
     *
     * @throws Exception
     */
    public function findAvailableLocales()
    {
        // Check for needed data
        $path   = $this->getPath();
        $domain = $this->getDomain();
        if (!$path || !$domain) {
            throw new Exception("Values for 'path' and 'domain' must be set.");
        }

        // Build regular expressions for finding domains and locales
        $regexLocale  = Locale::REGEX_LOCALE;
        $regexDefault = "/^{$regexLocale}\/LC_MESSAGES\/{$domain}\.mo$/ui";
        $regexVirtual = "/^(?<virtualLocale>C)\/LC_MESSAGES\/(?<virtualDomain>{$domain}[^a-z]{$regexLocale})\.mo$/ui";

        $glob = "{$path}/*/LC_MESSAGES/{$domain}*.mo";
        foreach (glob($glob) as $parse) {
            // Strip path prefix to fit regex
            $parse = str_replace($path . '/', '', $parse);

            // Check for default or virtual localized files
            if (preg_match($regexDefault, $parse, $found) || preg_match($regexVirtual, $parse, $found)) {
                $this->addLocale($found['locale']);
                if (!empty($found['virtualLocale'])) {
                    $map = [
                        'domain' => $found['virtualDomain'],
                        'locale' => $found['virtualLocale'],
                    ];
                    $this->addMap($found['locale'], $map);
                }
            }
        }

        return $this;
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
     * Get locale domain.
     *
     * @param bool $realDomain [optional] Get mapped value (if exists)
     *
     * @return string
     */
    public function getDomain($realDomain = false)
    {
        if ($realDomain) {
            return $this->getMap($this->getActive(), 'domain') ?: $this->domain;
        }

        return $this->domain;
    }

    /**
     * Get locale.
     *
     * @param bool $realLocale [optional] Get mapped value (if exists)
     *
     * @return string
     */
    public function getLocale($realLocale = false)
    {
        $active = $this->getActive();

        if (!$active) {
            return null;
        }

        if ($realLocale) {
            return $this->getMap($active, 'locale') ?: $active->getLocale();
        }

        return $active->getLocale();
    }

    /**
     * Get map for specified name.
     *
     * @param string|Locale $locale
     * @param string        $key
     *
     * @return array|null
     */
    public function getMap($locale, $key = null)
    {
        // Get sanitized locale
        $locale = Locale::sanitizeLocale($locale);

        if ($key) {
            return $this->maps[$locale][$key] ?? null;
        }

        return $this->maps[$locale] ?? null;
    }

    /**
     * Get maps.
     *
     * @return array
     */
    public function getMaps()
    {
        return $this->maps;
    }

    /**
     * Get locale path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function setActive($locale)
    {
        parent::setActive($locale);

        // Check for needed data
        $domain = $this->getDomain(true);
        $path   = $this->getPath();
        $locale = $this->getLocale(true);

        if (!$path || !$domain) {
            throw new Exception("Values for 'path' and 'domain' must be set.");
        }

        // Set locale (must be incl. code set!)
        $locale = $locale . '.' . self::CODE_SET;
        setlocale(LC_MESSAGES, $locale);

        // Bind (virtual) locale and domain for gettext() usage
        bindtextdomain($domain, $path);
        bind_textdomain_codeset($domain, self::CODE_SET);
        textdomain($domain);

        return $this;
    }

    /**
     * Set the default locale.
     *
     * @param string|Locale $locale
     *
     * @return Locales
     */
    public function setDefault($locale)
    {
        // Make sure locale is available (set it on list if not set yet)
        $this->addLocale($locale);

        // Mark as default locale
        $this->default = $this->findByLocale($locale);

        // Mark as current locale (if none set yet)
        if (!$this->getActive()) {
            $this->setActive($this->default);
        }

        return $this;
    }

    /**
     * Set domain.
     *
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = realpath($path);

        return $this;
    }
}
