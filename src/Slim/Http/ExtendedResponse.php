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
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Interfaces\Http\HeadersInterface;
use Slim\Route;

/**
 * Class ExtendedResponse
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedResponse extends Response
{
    /**
     * Get response contents directly.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string
     */
    public function getContents()
    {
        return (string) $this->getBody();
    }
}
