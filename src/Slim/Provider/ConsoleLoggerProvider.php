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
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
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
    public static function getDefaultSettings(): array
    {
        return [
            'name'      => 'console',
            'color'     => true,
            'level'     => Logger::DEBUG,
            'trimPaths' => [],
        ];
    }

    public static function buildLineFormatter(): LineFormatter
    {
        $formatter = new LineFormatter(
            "[%datetime%] %level_name% %message% %context% %extra%\n",
            "Y-m-d H:i:s"
        );
        $formatter->ignoreEmptyContextAndExtra();

        return $formatter;
    }

    /**
     * @throws Exception
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function register(Container $container): void
    {
        // Append custom settings with missing params from default settings
        $container['settings']['logger'] = self::mergeWithDefaultSettings($container['settings']['logger']);

        /**
         * @throws Exception
         */
        $container['logger'] = function (Container $c): Logger {
            $settings = $c['settings']['logger'];

            $logger = new Logger($settings['name']);
            if ($settings['color']) {
                $logger->pushProcessor(new ConsoleColorProcessor());
            }
            $logger->pushProcessor(new CleanupProcessor($settings['trimPaths']));
            $logger->pushProcessor(new IntrospectionProcessor(Logger::WARNING));
            $logger->pushProcessor(new PsrLogMessageProcessor());

            $logger->setTimezone(new DateTimeZone('Europe/Berlin'));
            $logger->useMicrosecondTimestamps(false); // Using microseconds is buggy (2016-08-04)

            $defaultHandler = new StreamHandler('php://stdout', $settings['level'], false);
            $defaultHandler->setFormatter(static::buildLineFormatter());
            $logger->pushHandler($defaultHandler);

            $errorHandler = new StreamHandler('php://stderr', Logger::ERROR, false);
            $errorHandler->setFormatter(static::buildLineFormatter());
            $logger->pushHandler($errorHandler);

            return $logger;
        };
    }
}
