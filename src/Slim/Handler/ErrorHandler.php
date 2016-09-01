<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Handler;

use Monolog\Logger;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Throwable;

/**
 * Class ErrorHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Logger $logger
 * @property array  $settings
 * @property Twig   $view
 */
class ErrorHandler extends AbstractHandler
{
    /**
     * Invoke handler.
     *
     * @param Request   $request  The most recent Request object
     * @param Response  $response The most recent Response object
     * @param Throwable $e        The caught Exception object
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public function __invoke(Request $request, Response $response, Throwable $e)
    {
        $this->logError($e);

        if ($this->view && !$this->settings['displayErrorDetails']) {
            $response = $response->withStatus(500);
            $response = $this->renderTemplate($request, $response, '_error');
        } else {
            $handler  = $this->container['defaultErrorHandler'];
            $response = $handler($request, $response, $e);
        }

        return $response;
    }

    /**
     * Write to the error log if displayErrorDetails is false.
     *
     * @param Throwable $e
     */
    protected function logError(Throwable $e)
    {
        if ($this->settings['displayErrorDetails']) {
            return;
        }

        if (!$this->logger) {
            return;
        }

        static $errorsLogged = [];

        // Get hash of Throwable object
        $errorObjectHash = spl_object_hash($e);

        // check: only log if we haven't logged this exact error before
        if (!isset($errorsLogged[$errorObjectHash])) {
            // Log
            $this->logger->error(get_class($e), ['exception' => $e]);

            // Save information that we have logged this error
            $errorsLogged[$errorObjectHash] = true;
        }
    }
}
