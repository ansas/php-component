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

use Ansas\Slim\Http\ExtendedRequest;
use Pimple\Container;

/**
 * Class ExtendedRequestProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedRequestProvider extends AbstractProvider
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
         * @return ExtendedRequest
         */
        $container['request'] = function ($c) {
            return ExtendedRequest::createFromEnvironment($c['environment']);
        };
    }
}
