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

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Uri;

/**
 * Class RemoveScriptNameFromUrl
 *
 * Remove script url (usually "/index.php") from url by redirecting to version without it.
 *
 * Can also be used to prevent duplicate content.
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class RemoveScriptNameFromUrl
{
    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        /** @var Uri $uri */
        $uri = $request->getUri();

        $basePath      = $uri->getBasePath();
        $requestTarget = $request->getRequestTarget();

        if (strlen($basePath) && 0 === strpos($requestTarget, $basePath)) {
            $requestTarget = mb_substr($requestTarget, mb_strlen($basePath));

            return $response->withHeader('Location', $requestTarget);
        }

        return $next($request, $response);
    }
}
