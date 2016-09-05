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

use Ansas\Component\Collection\Collection;
use Ansas\Util\Text;
use Exception;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;

/**
 * Trait TwigHandlerTrait
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Collection $data
 * @property Container  $container
 * @property Router     $router
 * @property array      $settings
 * @property Twig       $view
 */
trait TwigHandlerTrait
{
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
     * @param Request  $request
     * @param Response $response
     * @param string   $template The template to render
     * @param int      $status   [optional] Response status code
     *
     * @return Response
     * @throws Exception
     */
    public function renderTemplate(Request $request, Response $response, $template, $status = null)
    {
        if (!$this->view instanceof Twig) {
            throw new Exception("Twig provider not registered.");
        }

        foreach ($this->settings['view']['global'] as $key => $map) {
            $key = is_numeric($key) ? $map : $key;
            $key = Text::toLower($key);

            switch ($key) {
                case 'request':
                    $value = $request;
                    break;
                case 'response':
                    $value = $response;
                    break;
                default:
                    $value = $this->container[$key] ?? null;
                    break;
            }

            $this->view->getEnvironment()->addGlobal($map, $value);
        }

        if ($status) {
            $response = $response->withStatus($status);
        }

        $response = $this->view->render(
            $response,
            $template . $this->settings['view']['extension'],
            $this->data->all()
        );

        return $response;
    }
}
