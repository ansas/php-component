<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Cors
 *
 * Middleware to add CORS support (Cross-Origin Resource Sharing).
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @see     http://www.html5rocks.com/static/images/cors_server_flowchart.png
 * @see     https://github.com/palanik/CorsSlim/blob/master/CorsSlim.php
 */
class Cors
{
    /**
     * Execute the middleware.
     *
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', ['Content-Type'])
            ->withHeader('Access-Control-Allow-Methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])
        ;

        if ($request->isOptions()) {
            return $response;
        }

        // Call next middleware
        return $next($request, $response);
    }
}
