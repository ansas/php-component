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
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ForceRoute
{
    /**
     * @var string Path (route) to go to
     */
    protected $path;

    /**
     * @var array|null Query params
     */
    protected $params;

    /**
     * ForceRoute constructor.
     *
     * @param string     $path
     * @param array|null $params
     */
    public function __construct($path, array $params = null)
    {
        $this->path   = '/' . ltrim($path, '/');
        $this->params = $params;
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
        // Overwrite request with new uri path
        $uri     = $request->getUri();
        $request = $request->withUri($uri->withPath($this->path));

        // Overwrite params (if provided)
        if (!empty($this->params)) {
            $request = $request->withQueryParams($this->params);
        }

        // Call next middleware
        return $next($request, $response);
    }
}
