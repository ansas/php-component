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

use Ansas\Component\Locale\Localization;
use Ansas\Slim\ExtendedRouter;
use Ansas\Slim\Handler\ContainerInjectTrait;
use Ansas\Slim\Handler\NotFoundHandler;
use Ansas\Slim\Http\ExtendedRequest;
use Exception;
use Slim\Http\Response;
use Slim\Route;

/**
 * Class LanguageSwitch
 *
 * What this middleware does:
 *
 * - Checks languageIdentifier if given value is a valid (available) language
 * - Depending on flag: Redirects to default language path if none is set OR removes language path for default language
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Localization    locale
 * @property ExtendedRouter  router
 * @property NotFoundHandler notFoundHandler
 */
class LanguageSwitch
{
    use ContainerInjectTrait;

    /**
     * Execute the middleware.
     *
     * @param ExtendedRequest $request
     * @param Response        $response
     * @param callable        $next
     *
     * @return Response
     */
    public function __invoke(ExtendedRequest $request, Response $response, callable $next)
    {
        $router = $this->router;
        $route  = $request->getCurrentRoute();

        $locales = $this->locale;
        $locale  = $locales->getDefault();

        $lang = $route->getArgument($router->getLanguageIdentifier());
        $lang = ltrim($lang, '/');

        $redirect = false;
        if ($lang) {
            try {
                // Find language in list of available locales and set it as active
                $locale = $locales->findByLanguage($lang);
                $locales->setActive($locale);

                if ($router->isOmitDefaultLanguage() && $locale == $locales->getDefault()) {
                    // Trigger redirect to correct path (as language set in path is default lang and we want to omit it)
                    $redirect = true;
                }
            } catch (Exception $e) {
                // Trigger "notFound" as setActive() throws Exception if $locale is not valid / available
                $next = $this->notFoundHandler;
            }
        } elseif (!$router->isOmitDefaultLanguage()) {
            // Trigger redirect to correct path (as language is not set in path but we don't want to omit default lang)
            $redirect = true;
        }

        // Redirect to route with proper language identifier value set (or omitted)
        if ($redirect) {
            $path = $router->pathFor(
                $route->getName(),
                $route->getArguments(),
                $request->getParams(),
                $locale->getLanguage()
            );

            $uri = $request->getUri()->withPath($path);

            return $response->withRedirect($uri);
        }

        return $next($request, $response);
    }
}
