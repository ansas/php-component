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

use Slim\Http\Response;
use Slim\Router;

/**
 * Trait RedirectToRouteTrait
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Router router
 */
trait RedirectToRouteTrait
{
    /**
     * Redirect to specific route.
     *
     * @param  Response $response
     * @param  string   $route       The route to redirect to
     * @param  array    $data        [optional] Route params
     * @param  array    $queryParams [optional] Query string params
     * @param  string   $suffix      [optional] URL suffix like query string
     *
     * @return Response
     */
    public function redirectToRoute(Response $response, $route, array $data = [], array $queryParams = [], $suffix = '')
    {
        $url = $this->router->pathFor($route, $data, $queryParams) . $suffix;

        return $response->withRedirect($url, 301);
    }
}
