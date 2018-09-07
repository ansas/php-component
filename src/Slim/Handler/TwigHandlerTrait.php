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
     * Fetches template with previous set data.
     *
     * @param Request $request
     * @param string  $template The template to render
     *
     * @return string
     * @throws Exception
     */
    public function fetchTemplate(Request $request, $template)
    {
        if (!$this->view instanceof Twig) {
            throw new Exception("Twig provider not registered.");
        }

        foreach ($this->settings['view']['global'] as $key => $map) {
            $key = is_numeric($key) ? $map : $key;

            switch ($key) {
                case 'request':
                    $value = $request;
                    break;

                default:
                    $value = isset($this->container[$key]) ? $this->container[$key] : null;
                    break;
            }

            $this->view->getEnvironment()->addGlobal($map, $value);
        }

        $result = $this->view->fetch(
            $template . $this->settings['view']['extension'],
            $this->data->all()
        );

        return $result;
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
        $response->getBody()->write($this->fetchTemplate($request, $template));

        if ($status) {
            $response = $response->withStatus($status);
        }

        return $response;
    }
}
