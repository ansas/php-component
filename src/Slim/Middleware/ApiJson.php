<?php
/**
 * This file is part of the PHP components package.
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
 * Class ApiJson
 *
 * Middleware to make sure JSON is returned.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ApiJson
{
    /**
     * Execute middleware.
     *
     * @param Request  $request
     * @param Response $response
     * @param callable $next
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $request  = $request->withHeader('Accept', 'application/json');
        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

        return $next($request, $response);
    }
}
