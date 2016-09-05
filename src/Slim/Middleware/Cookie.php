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

use Ansas\Slim\Handler\ContainerInjectTrait;
use Ansas\Slim\Handler\CookieHandler;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Cookie
 *
 * Middleware to get and set cookies.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property CookieHandler $cookie
 */
class Cookie
{
    use ContainerInjectTrait;

    /** HTTP response header name */
    const HEADER = 'Set-Cookie';

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

        foreach ($this->cookie->toHeaders() as $cookie) {
            /** @var Response $response */
            $response = $response->withAddedHeader(static::HEADER, $cookie);
        }

        return $response;
    }
}
