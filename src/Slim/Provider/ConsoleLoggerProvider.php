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
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'name'      => 'console',
            'color'     => true,
            'level'     => Logger::DEBUG,
            'trimPaths' => [],
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

            $loggerFormat     = "[%datetime%] %level_name% %message% %context% %extra%\n";
            $loggerTimeFormat = "Y-m-d H:i:s";
            $loggerTimeZone   = new DateTimeZone('Europe/Berlin');

            $logger = new Logger($settings['name']);
            if ($settings['color']) {
                $logger->pushProcessor(new ConsoleColorProcessor());
            }
            $logger->pushProcessor(new CleanupProcessor($settings['trimPaths']));
            $logger->pushProcessor(new IntrospectionProcessor(Logger::WARNING));
            $logger->pushProcessor(new ProcessIdProcessor());
            $logger->pushProcessor(new PsrLogMessageProcessor());

            $logger->setTimezone($loggerTimeZone);
            $logger->useMicrosecondTimestamps(false); // Using microseconds is buggy (2016-08-04)

            $formatter = new LineFormatter($loggerFormat, $loggerTimeFormat);
            $formatter->ignoreEmptyContextAndExtra(true);

            $defaultHandler = new StreamHandler('php://stdout', $settings['level'], $bubble = false);
            $defaultHandler->setFormatter($formatter);
            $logger->pushHandler($defaultHandler);

            $errorHandler = new StreamHandler('php://stderr', Logger::ERROR, $bubble = false);
            $errorHandler->setFormatter($formatter);
            $logger->pushHandler($errorHandler);

            return $logger;
        };
    }
}
