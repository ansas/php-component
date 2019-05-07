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
use Exception;
use Slim\Http\Response;

/**
 * Trait InvokeSubControllerTrait
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
trait InvokeSubControllerTrait
{
    use InvokeControllerTrait {
        __invoke as protected __invokeSimple;
    }

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

        // Map sub directories
        $subClass  = $method;
        $subMethod = null;
        if (false !== strpos($method, '/')) {
            list($subClass, $subMethod) = explode('/', $method, 2);
        }

        $class = preg_replace(
            '/Controller$/u',
            '\\' . ucfirst($subClass) . 'Controller',
            static::class
        );

        // Variant 1: url sub/dir is handled by sub/class
        if (class_exists($class)) {
            $args['method'] = $subMethod;

            $controller = new $class($this->getContainer());

            if (method_exists($controller, '__invoke')) {
                return $controller($request, $response, $args);
            }

            if (!$subMethod || !is_callable([$controller, $subMethod])) {
                $subMethod = 'notFound';
            };

            return $controller->$subMethod($request, $response);
        }

        // Variant 2: url sub/dir is handled by subMethod in current class
        return $this->__invokeSimple($request, $response, $args);
    }
}
