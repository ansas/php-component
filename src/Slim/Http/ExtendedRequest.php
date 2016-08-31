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
        return $this->headers->get('HTTP_REFERER');
    }

    /**
     * Get User Agent.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->headers->get('HTTP_USER_AGENT');
    }
}
