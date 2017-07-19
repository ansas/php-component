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

use Propel\Runtime\Propel;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Queries
 *
 * Middleware to add propel queries executed to response header.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Queries
{
    /** HTTP response header name */
    const HEADER = 'X-App-Queries';

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
        /** @var Response $response */
        $response = $next($request, $response);


        // Add header Enable propel debug mode so that queries are counted
        $con = Propel::getConnection();
        if (method_exists($con, 'getQueryCount')) {
            $response = $response->withHeader(self::HEADER, $con->getQueryCount());
        }

        return $response;
    }
}
