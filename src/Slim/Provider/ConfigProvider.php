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

use Ansas\Slim\Handler\ConfigHandler;
use Pimple\Container;

/**
 * Class ConfigProvider
 *
 * - Enables config (settings) override
 *
 * @package App\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ConfigProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'path'   => '.',
            'key'    => 'settings',
            'suffix' => '.php',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['config'] = self::mergeWithDefaultSettings($container['settings']['config']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return ConfigHandler
         */
        $container['config'] = function (Container $c) {
            $settings = $c['settings']['config'];

            $config = ConfigHandler
                ::create($c['settings'])
                ->setPath($settings['path'])
                ->setSuffix($settings['suffix'])
                ->setKey($settings['key'])
            ;

            return $config;
        };
    }
}
