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
use Ansas\Slim\Handler\FlashHandler;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class FlashAutoKeepOnRedirect
 *
 * IMPORTANT: Include BEFORE cookie middleware!
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property FlashHandler flash
 */
class FlashAutoKeepOnRedirect
{
    use ContainerInjectTrait;

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
        $hasFlashOnPageLoad = $this->flash->count(FlashHandler::NOW);

        /** @var Response $response */
        $response = $next($request, $response);

        if ($hasFlashOnPageLoad && $response->isRedirection()) {
            $this->flash->keep();
        }

        return $response;
    }
}
