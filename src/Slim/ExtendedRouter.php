<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim;

use Ansas\Component\Locale\Localization;
use FastRoute\RouteParser;
use InvalidArgumentException;
use Slim\Router;

/**
 * Class ExtendedRouter
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedRouter extends Router
{
    protected $languageIdentifier;

    protected $localization;

    /**
     * Create new instance.
     *
     * @param RouteParser $parser
     *
     * @return static
     */
    public static function create(RouteParser $parser = null)
    {
        return new static($parser);
    }

    /**
     * {@inheritdoc}
     */
    public function pathFor($name, array $data = [], array $queryParams = [], $lang = null)
    {
        if (!$name) {
            throw new InvalidArgumentException("Invalid empty data path name");
        }

        $identifier = $this->getLanguageIdentifier();
        $locales = $this->getLocalization();

        if ($identifier && $locales) {
            $lang = $lang ?: $data[$identifier] ?? null;
            if ($lang) {
                $locale = $locales->find($lang);
                if (!$locale) {
                    throw new InvalidArgumentException(sprintf("Invalid data '%s' for URL argument '%s'", $lang, $identifier));
                }
            } else {
                $locale = $locales->getActive();
            }
            $data[$identifier] = $locale->getLanguage();
        }

        return parent::pathFor($name, $data, $queryParams);
    }

    /**
     * {@inheritdoc}
     *
     * This method override just fixes the wrong return type "self" instead of "$this"
     *
     * @return $this
     */
    public function setCacheFile($cacheFile)
    {
        parent::setCacheFile($cacheFile);

        return $this;
    }

    /**
     * Set the language identifier string for building path names.
     *
     * @param string|null $languageIdentifier [optional]
     *
     * @return $this
     */
    public function setLanguageIdentifier($languageIdentifier = null)
    {
        $this->languageIdentifier = $languageIdentifier;

        return $this;
    }

    /**
     * Get the language identifier.
     *
     * @return string|null
     */
    public function getLanguageIdentifier()
    {
        return $this->languageIdentifier;
    }

    /**
     * @param Localization|null $localization [optional]
     *
     * @return $this
     */
    public function setLocalization(Localization $localization = null)
    {
        $this->localization = $localization;

        return $this;
    }

    /**
     * @return Localization|null
     */
    public function getLocalization()
    {
        return $this->localization;
    }
}
