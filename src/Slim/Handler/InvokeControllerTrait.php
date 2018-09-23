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

use Ansas\Slim\Http\ExtendedRequest;
use Ansas\Util\Path;
use Exception;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Response;

/**
 * Trait InvokeControllerTrait
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
trait InvokeControllerTrait
{
    /**
     * Invoke controller.
     *
     * @param ExtendedRequest $request  The most recent Request object
     * @param Response        $response The most recent Response object
     * @param array           $args
     *
     * @return Response
     * @throws Exception
     */
    public function __invoke(ExtendedRequest $request, Response $response, array $args)
    {
        $method = $args['method'] ?? $this->getInvokeFallbackMethod();
        $method = Path::toCamelCase($method);
        $method = $method && is_callable([$this, $method]) ? $method : 'notFound';

        return $this->$method($request, $response);
    }

    /**
     * @return string
     */
    protected function getInvokeFallbackMethod()
    {
        return '';
    }

    /**
     * Get Container.
     *
     * @return ContainerInterface|Container
     */
    abstract public function getContainer();
}
