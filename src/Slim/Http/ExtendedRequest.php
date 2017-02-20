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

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Interfaces\Http\HeadersInterface;
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
     * {@inheritDoc}
     *
     * This method overrides "invalid request method" handling to force methodNotAllowed handler instead of throwing an
     * (uncaught) exception.
     */
    public function __construct(
        $method,
        UriInterface $uri,
        HeadersInterface $headers,
        array $cookies,
        array $serverParams,
        StreamInterface $body,
        array $uploadedFiles
    ) {
        // Get method or set to UNKNOWN if not determinable
        $method = is_string($method) && $method ? strtoupper($method) : "UNKNOWN";

        // Hack: make every method valid so logic uses methodNotAllowed handler instead of throwing (uncaught) exception
        $this->validMethods[$method] = 1;

        parent::__construct($method, $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);
    }

    /**
     * {@inheritDoc}
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
     * Retrieve the host component of the URI.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getHost()
    {
        return mb_strtolower($this->getUri()->getHost());
    }

    /**
     * Retrieve the host component of the URI.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getHostAce()
    {
        return idn_to_ascii($this->getHost());
    }

    /**
     * Retrieve the host component of the URI.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getHostIdn()
    {
        return idn_to_utf8($this->getHost());
    }

    /**
     * Get request ip.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getIp()
    {
        $keys = ['X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            $ip = $this->getServerParam($key);

            if (!$ip) {
                continue;
            }

            $ip = preg_replace('/,.*$/u', '', $ip);
            $ip = trim($ip);

            return $ip;
        }

        return null;
    }

    /**
     * Fetch filtered associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|string $filter Array or comma separated list
     *
     * @return array
     */
    public function getParamsWith($filter)
    {
        // Convert $filter to array if necessary
        if (!is_array($filter)) {
            $filter = preg_split("/, */", $filter, -1, PREG_SPLIT_NO_EMPTY);
        }

        $params = $this->getParams();
        $filter = array_flip($filter);

        return array_intersect_key($params, $filter);
    }

    /**
     * Fetch filtered associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|string $filter Array or comma separated list
     *
     * @return array
     */
    public function getParamsWithout($filter)
    {
        // Convert $filter to array if necessary
        if (!is_array($filter)) {
            $filter = preg_split("/, */", $filter, -1, PREG_SPLIT_NO_EMPTY);
        }

        $params = array_keys($this->getParams());
        $filter = array_merge(array_diff($params, $filter), array_diff($filter, $params));

        return $this->getParamsWith($filter);
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
     * @deprecated Use <code>getRequestTarget()</code> instead.
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->getRequestTarget();
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

    /**
     * Returns an instance with the provided host.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $host
     *
     * @return static
     */
    public function withHost($host)
    {
        $uri = $this->getUri()->withHost($host);

        /** @var static $clone */
        $clone = $this->withUri($uri);

        return $clone;
    }
}
