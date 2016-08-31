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
    public function register(Container $container)
    {
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

            $c['logger']->debug("PDO loaded");

            return $pdo;
        };
    }
}
