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

use Ansas\Monolog\Profiler;
use Pimple\Container;

/**
 * Class ProfilerProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ProfilerProvider extends AbstractProvider
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
         * @return Profiler
         */
        $container['profiler'] = function (Container $c) {

            $profiler = new Profiler($c['logger']);
            $profiler->start();

            $c['logger']->debug("Profiler loaded");

            return $profiler;
        };
    }
}
