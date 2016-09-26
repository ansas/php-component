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

use PDO;
use Pimple\Container;

/**
 * Class PdoProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PdoProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'dsn'      => '',
            'user'     => '',
            'password' => '',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['database'] = self::mergeWithDefaultSettings($container['settings']['database']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return PDO
         */
        $container['pdo'] = function (Container $c) {

            $settings = $c['settings']['database'];

            $pdo = new PDO($settings['dsn'], $settings['user'], $settings['password']);

            $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        };
    }
}
