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

use Ansas\Slim\Handler\NotAllowedHandler;
use Pimple\Container;

/**
 * Class NotAllowedProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class NotAllowedProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Save default handler under new name
        $container['defaultNotAllowedHandler'] = $container->raw('notAllowedHandler');

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return NotAllowedHandler
         */
        $container['notAllowedHandler'] = function ($c) {
            return new NotAllowedHandler($c);
        };
    }
}
