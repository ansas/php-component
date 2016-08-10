<?php
/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Controller;

use Ansas\Component\Collection\Collection;
use Ansas\Monolog\Profiler;
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

    /**
     * Redirect to specific route.
     *
     * @param  Response $response
     * @param  string   $route  The route to redirect to
     * @param  array    $params [optional] Route params
     * @param  string   $suffix [optional] URL suffix like query string
     *
     * @return Response
     */
    public function redirectToRoute(Response $response, $route, $params = [], $suffix = '')
    {
        $url = $this->router->pathFor($route, $params) . $suffix;

        return $response->withRedirect($url, 301);
    }

    /**
     * Renders template with previous set data.
     *
     * @param  Response $response
     * @param  string   $template The template to render
     * @param  int      $status   [optional] Response status code
     *
     * @return Response
     */
    public function renderTemplate(Response $response, $template, $status = null)
    {
        if ($status) {
            $response = $response->withStatus($status);
        }

        return $this->view->render($response, $template . $this->settings['view']['extension'], $this->data->all());
    }
}
