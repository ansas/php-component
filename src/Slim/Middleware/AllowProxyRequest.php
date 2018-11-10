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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AllowProxyRequest
 *
 * Override URI data with proxy header data
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class AllowProxyRequest
{
    /**
     * @var array Supported schemes with default ports
     */
    protected static $validSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $uri = $request->getUri();

        if ($request->hasHeader('X-Forwarded-Proto')) {
            $scheme = $request->getHeaderLine('X-Forwarded-Proto');
            $scheme = trim($scheme);
            $scheme = strtolower($scheme);

            $uri = $uri->withScheme($scheme);
        }

        if ($request->hasHeader('X-Forwarded-Host')) {
            $host = $request->getHeaderLine('X-Forwarded-Host');
            $host = preg_replace('/,.*$/u', '', $host);
            list($host, $port) = array_pad(explode(':', $host, 2), 2, null);
            $host = trim($host);
            $port = (int) $port;

            if (!$port) {
                $scheme = $uri->getScheme();
                $port   = static::$validSchemes[$scheme];
            }

            $uri = $uri->withHost($host)->withPort($port);
        }

        if ($request->hasHeader('X-Forwarded-Port')) {
            $port = $request->getHeaderLine('X-Forwarded-Port');
            $port = preg_replace('/,.*$/u', '', $port);
            $port = (int) $port;

            $uri = $uri->withPort($port);
        }

        $request = $request->withUri($uri);

        return $next($request, $response);
    }
}
