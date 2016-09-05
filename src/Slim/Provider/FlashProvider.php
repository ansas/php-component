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

use Ansas\Slim\Handler\FlashHandler;
use Pimple\Container;

/**
 * Class FlashProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FlashProvider extends AbstractProvider
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
         * @return FlashHandler
         */
        $container['flash'] = function (Container $c) {
            return new FlashHandler($c['cookie']);
        };
    }
}
