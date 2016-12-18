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
 * Class Runtime
 *
 * Middleware to add script execution time (runtime).
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Runtime
{
    /** HTTP response header name */
    const HEADER = 'X-Runtime';

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
        $server      = $request->getServerParams();
        $requestTime = isset($server['REQUEST_TIME_FLOAT']) ? $server['REQUEST_TIME_FLOAT'] : microtime(true);

        // Call next middleware
        $response = $next($request, $response);

        $executionTime = microtime(true) - $requestTime;

        return $response->withHeader(self::HEADER, sprintf('%.3f', $executionTime));
    }
}
