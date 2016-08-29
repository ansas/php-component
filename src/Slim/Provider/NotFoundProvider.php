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

use Ansas\Slim\Handler\NotFoundHandler;
use Pimple\Container;

/**
 * Class NotFoundProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class NotFoundProvider extends AbstractProvider
{
    /**
     * Register Profiler.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        // Save default handler under new name
        $container['defaultNotFoundHandler'] = $container->raw('notFoundHandler');

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return NotFoundHandler
         */
        $container['notFoundHandler'] = function ($c) {
            return new NotFoundHandler($c);
        };
    }
}
