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

use Ansas\Util\Number;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Memory
 *
 * Middleware to add script max used ram (memory) to response header.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Memory
{
    /** HTTP response header name */
    const HEADER     = 'X-App-Memory';

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
        // Call next middleware
        /** @var Response $response */
        $response = $next($request, $response);

        $memory = memory_get_peak_usage(true);

        return $response->withHeader(self::HEADER, Number::toReadableSize($memory));
    }
}
