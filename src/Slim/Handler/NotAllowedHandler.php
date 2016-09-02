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

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;

/**
 * Class NotAllowedHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property array $settings
 * @property Twig  $view
 */
class NotAllowedHandler extends AbstractHandler
{
    /**
     * Invoke handler.
     *
     * @param  Request  $request  The most recent Request object
     * @param  Response $response The most recent Response object
     * @param  string[] $methods  Allowed HTTP methods
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $methods)
    {
        $code     = 405;
        $template = $this->settings['view']['status'][$code] ?? null;
        $isHtml   = stripos($request->getHeaderLine('Accept'), 'html') !== false;

        if ($template && $isHtml) {
            $response = $this->renderTemplate($request, $response, $template, $code);
        } else {
            $handler  = $this->container['defaultNotAllowedHandler'];
            $response = $handler($request, $response, $methods);
        }

        return $response;
    }
}
