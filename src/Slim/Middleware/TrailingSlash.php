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
 * Class TrailingSlash
 *
 * Redirect to version with / without slash instead of calling notFoundHandler.
 * Configure whether to add or remove the slash (optional). The default behavior
 * is to remove the slash.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class TrailingSlash
{
    /** Add slash to url */
    const SLASH_ADD = true;

    /** Remove slash from url */
    const SLASH_REMOVE = false;

    /**
     * @var bool Add or remove the slash
     */
    private $addSlash;

    /**
     * TrailingSlash constructor.
     *
     * Configure whether add or remove the slash.
     *
     * @param bool $addSlash [optional]
     */
    public function __construct($addSlash = self::SLASH_REMOVE)
    {
        $this->addSlash = (bool)$addSlash;
    }

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
        $uri  = $request->getUri();
        $path = $uri->getPath();

        // Add or remove slash as configured
        if ($path != '/') {
            $path = rtrim($path, '/');
            if ($this->addSlash && !pathinfo($path, PATHINFO_EXTENSION)) {
                $path .= '/';
            }
        }

        // Redirect
        if ($uri->getPath() !== $path) {
            return $response->withRedirect($uri->withPath($path), 301);
        }

        return $next($request, $response);
    }
}
