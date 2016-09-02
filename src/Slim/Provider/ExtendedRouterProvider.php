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
    public function register(Container $container)
    {
        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return ExtendedRouter
         */
        $container['router'] = function ($c) {
            $routerCacheFile    = $c['settings']['routerCacheFile'] ?? false;
            $languageIdentifier = $c['settings']['locale']['identifier'] ?? null;
            $locale             = $c['locale'] ?? null;

            $router = ExtendedRouter
                ::create()
                ->setCacheFile($routerCacheFile)
                ->setLanguageIdentifier($languageIdentifier)
                ->setLocalization($locale)
            ;

            return $router;
        };
    }
}
