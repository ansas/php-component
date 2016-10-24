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
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Propel;
use Propel\Runtime\ServiceContainer\StandardServiceContainer;

class PropelProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'adapter'    => 'mysql',
            'classname'  => null,
            'connection' => 'default',
            'version'    => '2.0.0-dev',
            'dsn'        => '',
            'user'       => '',
            'password'   => '',
            'logger'     => [
                'path'  => null,
                'level' => Logger::DEBUG,
            ],
            'settings'   => [
                'charset' => null,
                'queries' => [],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['database'] = self::mergeWithDefaultSettings($container['settings']['database']);

        $settings = $container['settings']['database'];

        $logLevel  = $settings['logger']['level'] ?? null;
        $logPath   = $settings['logger']['path'] ?? null;
        $className = $settings['classname'] ?? null;

        if (!$className) {
            $className = 'Propel\\Runtime\\Connection\\ConnectionWrapper';
            if ($logLevel == Logger::DEBUG) {
                $className = 'Propel\\Runtime\\Connection\\ProfilerConnectionWrapper';
            }
        }

        $manager = new ConnectionManagerSingle();
        $manager->setConfiguration([
            'classname' => $className,
            'dsn'       => $settings['dsn'],
            'user'      => $settings['user'],
            'password'  => $settings['password'],
            'settings'  => $settings['settings'],
        ]);
        $manager->setName($settings['connection']);

        /** @var StandardServiceContainer $serviceContainer */
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->checkVersion($settings['version']);
        $serviceContainer->setAdapterClass($settings['connection'], $settings['adapter']);
        $serviceContainer->setConnectionManager($settings['connection'], $manager);
        $serviceContainer->setDefaultDatasource($settings['connection']);

        if ($logPath && $logLevel) {
            $logger = new Logger('defaultLogger');
            $logger->pushHandler(new StreamHandler($logPath, $logLevel));

            $serviceContainer->setLogger('defaultLogger', $logger);

            if ($logLevel == Logger::DEBUG) {
                /** @var ConnectionWrapper $con */
                $con = Propel::getConnection();
                $con->useDebug(true);
            }
        }
    }
}
