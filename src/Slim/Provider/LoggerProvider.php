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
     * Get default settings.
     *
     * @return array
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
     * Register Profiler.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $config = array_merge([], self::getDefaultSettings(), $container['settings']['logger']);

        /**
         * Add dependency (DI).
         *
         * @return Logger
         */
        $container['logger'] = function () use ($config) {
            $logger = new Logger($config['name']);
            $logger->pushHandler(new StreamHandler($config['path'], $config['level']));

            return $logger;
        };
    }
}
