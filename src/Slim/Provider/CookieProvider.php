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

use Ansas\Slim\Handler\CookieHandler;
use Pimple\Container;

/**
 * Class CookieProvider
 *
 * @package App\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CookieProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'prefix'  => '',
            'default' => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['cookie'] = self::mergeWithDefaultSettings($container['settings']['cookie']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return CookieHandler
         */
        $container['cookie'] = function (Container $c) {
            $settings = $c['settings']['cookie'];

            $cookie = CookieHandler
                ::create($c['request']->getCookieParams())
                ->setDefaults($settings['default'])
                ->setPrefix($settings['prefix'])
            ;

            return $cookie;
        };
    }
}
