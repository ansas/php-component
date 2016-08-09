<?php
/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Provider;

use Ansas\Component\Collection\Collection;
use Pimple\Container;

/**
 * Class DataProvider
 *
 * @package App\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class DataProvider extends AbstractProvider
{
    /**
     * Register provider.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        /**
         * Add dependency (DI).
         *
         * @return Collection
         */
        $container['data'] = function () {
            return new Collection();
        };
    }
}
