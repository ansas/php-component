<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Provider;

use Ansas\Slim\ExtendedRouter;
use Pimple\Container;

/**
 * Class ExtendedRouterProvider
 *
 * Extends the default router for localization support.
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedRouterProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'languageIdentifier'  => 'lang',
            'omitDefaultLanguage' => false,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['router'] = self::mergeWithDefaultSettings($container['settings']['router']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return ExtendedRouter
         */
        $container['router'] = function ($c) {
            $locale    = $c['locale'] ?? null;
            $cacheFile = $c['settings']['routerCacheFile'] ?? false;
            $settings  = $c['settings']['router'];

            $router = ExtendedRouter
                ::create()
                ->setCacheFile($cacheFile)
                ->setLanguageIdentifier($settings['languageIdentifier'])
                ->setLocalization($locale)
                ->setOmitDefaultLanguage($settings['omitDefaultLanguage'])
            ;

            return $router;
        };
    }
}
