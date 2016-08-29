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

use Ansas\Monolog\Processor\CleanupProcessor;
use Ansas\Monolog\Processor\ConsoleColorProcessor;
use DateTimeZone;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Pimple\Container;

/**
 * Class ConsoleLoggerProvider
 *
 * <code>composer require monolog/monolog</code>
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ConsoleLoggerProvider extends AbstractProvider
{
    /**
     * Get default settings.
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [
            'name'      => 'console',
            'level'     => Logger::DEBUG,
            'trimPaths' => [],
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

            $loggerFormat     = "[%datetime%] %level_name% %message% %context% %extra%\n";
            $loggerTimeFormat = "Y-m-d H:i:s";
            $loggerTimeZone   = new DateTimeZone('Europe/Berlin');

            $logger = new Logger($config['name']);
            $logger->pushProcessor(new ConsoleColorProcessor());
            $logger->pushProcessor(new CleanupProcessor($config['trimPaths']));
            $logger->pushProcessor(new IntrospectionProcessor());
            $logger->pushProcessor(new ProcessIdProcessor());
            $logger->pushProcessor(new PsrLogMessageProcessor());

            $logger->setTimezone($loggerTimeZone);
            $logger->useMicrosecondTimestamps(false); // Using microseconds is buggy (2016-08-04)

            $formatter = new LineFormatter($loggerFormat, $loggerTimeFormat);
            $formatter->ignoreEmptyContextAndExtra(true);

            $defaultHandler = new StreamHandler('php://stdout', $config['level'], $bubble = false);
            $defaultHandler->setFormatter($formatter);
            $logger->pushHandler($defaultHandler);

            $errorHandler = new StreamHandler('php://stderr', Logger::ERROR, $bubble = false);
            $errorHandler->setFormatter($formatter);
            $logger->pushHandler($errorHandler);

            // Register logger as default PHP error, exception and shutdown handler
            // Note: Make sure only this handler handles errors (set $callPrevious to false)
            $errorHandler = ErrorHandler::register($logger, $errorLevelMap = false, $exceptionLevelMap = false);
            $errorHandler->registerErrorHandler($levelMap = [], $callPrevious = false);
            $errorHandler->registerExceptionHandler($levelMap = [], $callPrevious = false);

            $logger->debug("Logger loaded");

            return $logger;
        };
    }
}
