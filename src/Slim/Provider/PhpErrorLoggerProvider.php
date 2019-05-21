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

use Monolog\ErrorHandler;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class PhpErrorLoggerProvider
 *
 * Register logger as default PHP error, exception and shutdown handler.
 *
 * Note: by default make sure only this handler handles errors (no bubble).
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PhpErrorLoggerProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'bubble' => false,
            'level'  => E_ALL,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['phpError'] = self::mergeWithDefaultSettings($container['settings']['phpError']);

        /** @var Logger $logger */
        $logger = $container['logger'];

        $settings = $container['settings']['phpError'];

        $errorHandler = new ErrorHandler($logger);
        $errorHandler->registerErrorHandler([], $settings['bubble'], $settings['level']);
        $errorHandler->registerExceptionHandler(null, $settings['bubble']);
        $errorHandler->registerFatalHandler();
    }
}
