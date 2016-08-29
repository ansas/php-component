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
        $acceptHeader = $request->getHeaderLine('Accept');
        if (stripos($acceptHeader, 'html') !== false) {
            return $this->view->render(
                $response->withStatus(404),
                '_notfound' . $this->settings['view']['extension']
            );
        }

        $handler = $this->container['defaultNotFoundHandler'];

        return $handler($request, $response);
    }
}
