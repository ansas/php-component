<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Http;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Route;

/**
 * Class ExtendedRequest
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedRequest extends Request
{
    /**
     * {@inheritdoc}
     *
     * This method override just fixes the wrong return type "self" instead of "static"
     *
     * @return static
     */
    public static function createFromEnvironment(Environment $environment)
    {
        return parent::createFromEnvironment($environment);
    }

    /**
     * Get current route.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return Route|null
     */
    public function getCurrentRoute()
    {
        return $this->getAttribute('route');
    }

    /**
     * Get full path (incl. basePath) but without query string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getFullPath()
    {
        /** @var Uri $uri */
        $uri = $this->getUri();

        return rtrim($uri->getBasePath(), '/') . '/' . ltrim($uri->getPath(), '/');
    }

    /**
     * Get full url (incl. basePath and path) but without query string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getFullUrl()
    {
        /** @var Uri $uri */
        $uri = $this->getUri();

        return rtrim($uri->getBaseUrl(), '/') . '/' . ltrim($uri->getPath(), '/');
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getIp()
    {
        $serverParams = $this->getServerParams();
        $keys         = ['X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            if (isset($serverParams[$key])) {
                return $serverParams[$key];
            }
        }

        return null;
    }

    /**
     * Get Referrer.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getReferrer()
    {
        /** @noinspection SpellCheckingInspection */
        return $this->getHeaderLine('HTTP_REFERER');
    }

    /**
     * Get Request URI.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getRequestUri()
    {
        /** @var Uri $uri */
        $uri = $this->getUri();

        $path  = $this->getFullPath();
        $query = $uri->getQuery();

        return $path . ($query ? '?' . $query : '');
    }

    /**
     * Get "ACCEPT_LANGUAGE" header.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getAcceptLanguage()
    {
        return $this->getHeaderLine('ACCEPT_LANGUAGE');
    }

    /**
     * Get "HTTP_USER_AGENT" header.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->getHeaderLine('HTTP_USER_AGENT');
    }
}
