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

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class LoggerProvider
 *
 * <code>composer require monolog/monolog</code>
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class LoggerProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'name'  => 'app',
            'path'  => './app.log',
            'level' => Logger::DEBUG,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['logger'] = self::mergeWithDefaultSettings($container['settings']['logger']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return Logger
         */
        $container['logger'] = function (Container $c) {
            $settings = $c['settings']['logger'];

            $logger = new Logger($settings['name']);
            $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));

            return $logger;
        };
    }
}
