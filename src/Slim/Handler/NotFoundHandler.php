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
 * Class NotFoundHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property array $settings
 * @property Twig  $view
 */
class NotFoundHandler extends AbstractHandler
{
    use TwigHandlerTrait;

    /**
     * Invoke handler.
     *
     * @param  Request  $request  The most recent Request object
     * @param  Response $response The most recent Response object
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response)
    {
        $code     = 404;
        $template = isset($this->settings['view']['status'][$code]) ? $this->settings['view']['status'][$code] : null;
        $isHtml   = stripos($request->getHeaderLine('Accept'), 'html') !== false;

        if ($template && $isHtml) {
            $response = $this->renderTemplate($request, $response, $template, $code);
        } else {
            $handler  = $this->container['defaultNotFoundHandler'];
            $response = $handler($request, $response);
        }

        return $response;
    }
}
