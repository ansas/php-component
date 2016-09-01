<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Handler;

use Pimple\Container;

/**
 * Class AbstractHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
abstract class AbstractHandler
{
    use TwigHandlerTrait;

    /**
     * @var Container Container
     */
    protected $container;

    /**
     * AbstractHandler constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Magic getter for easier access to container.
     *
     * <code>$this->logger->info('hello world!');</code>
     *
     * @param  string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->container[$name] ?? null;
    }
}
