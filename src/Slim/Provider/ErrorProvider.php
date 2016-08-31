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

use Ansas\Slim\Handler\ErrorHandler;
use Pimple\Container;

/**
 * Class ErrorProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ErrorProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        $container['defaultErrorHandler'] = $container->raw('phpErrorHandler');

        $container['errorHandler']    = function ($container) {
            return new ErrorHandler($container);
        };
        $container['phpErrorHandler'] = function ($container) {
            return new ErrorHandler($container);
        };
    }
}
