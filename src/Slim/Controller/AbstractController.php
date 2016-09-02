<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Controller;

use Ansas\Component\Collection\Collection;
use Ansas\Monolog\Profiler;
use Ansas\Slim\Handler\TwigHandlerTrait;
use Monolog\Logger;
use PDO;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;

/**
 * Class AbstractController
 *
 * @package Ansas\Slim\Controller
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Collection $data
 * @property Logger     $logger
 * @property PDO        $pdo
 * @property Profiler   $profiler
 * @property Router     $router
 * @property array      $settings
 * @property Twig       $view
 */
abstract class AbstractController
{
    use TwigHandlerTrait;

    /**
     * @var Container Container
     */
    protected $container;

    /**
     * AbstractController constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create new instance.
     *
     * @param Container $container
     *
     * @return static
     */
    public static function create(Container $container)
    {
        return new static($container);
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
     * Not found.
     *
     * @param  Request  $request
     * @param  Response $response
     *
     * @return Response
     */
    public function notFound(Request $request, Response $response)
    {
        $handler = $this->container->get('notFoundHandler');

        return $handler($request, $response);
    }

}
