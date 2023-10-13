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
 * @property Collection $data
 * @property Container  $container
 * @property Router     $router
 * @property array      $settings
 * @property Twig       $view
 */
trait TwigHandlerTrait
{
    /**
     * Fetches template with previous set data
     *
     * @throws Exception
     */
    public function fetchTemplate(Request $request, string $template, array $data = [], bool $appendData = true): string
    {
        if (!$this->view instanceof Twig) {
            throw new Exception("Twig provider not registered.");
        }

        if ($appendData) {
            $data += $this->data->all();
        }

        foreach ($this->settings['view']['global'] as $key => $map) {
            $key = is_numeric($key) ? $map : $key;

            $value = match ($key) {
                'request' => $request,
                default   => isset($this->container[$key]) ? $this->container[$key] : null,
            };

            $this->view->getEnvironment()->addGlobal($map, $value);
        }

        $result = $this->view->fetch($template . $this->settings['view']['extension'], $data);

        return trim($result);
    }

    /**
     * Renders template with previous set data
     *
     * @throws Exception
     */
    public function renderTemplate(Request $request, Response $response, string $template, ?int $status = null): Response
    {
        $response->getBody()->write($this->fetchTemplate($request, $template));

        if ($status) {
            $response = $response->withStatus($status);
        }

        return $response;
    }
}
