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

use Interop\Container\ContainerInterface;

/**
 * Trait ContainerInjectTrait
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
trait ContainerInjectTrait
{
    /**
     * @var ContainerInterface Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
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

    /**
     * Create new instance.
     *
     * @param ContainerInterface $container
     *
     * @return static
     */
    public static function create(ContainerInterface $container)
    {
        return new static($container);
    }

    /**
     * Get Container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set Container.
     *
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
