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
 * Class Memory
 *
 * Middleware to add script max used ram (memory).
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Memory
{
    /** HTTP response header name */
    const HEADER     = 'X-Memory';

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
        // Call next middleware
        $response = $next($request, $response);

        $memory = memory_get_peak_usage(true);


        return $response->withHeader(self::HEADER, $this->readableSize($memory));
    }

    /**
     * @param int    $bytes
     * @param int    $decimals [optional]
     * @param string $system   [optional] binary | metric
     *
     * @return string
     */
    protected function readableSize($bytes, $decimals = 1, $system = 'metric')
    {
        $mod = ($system === 'binary') ? 1024 : 1000;

        $units = [
            'binary' => [
                'B',
                'KiB',
                'MiB',
                'GiB',
                'TiB',
                'PiB',
                'EiB',
                'ZiB',
                'YiB',
            ],
            'metric' => [
                'B',
                'kB',
                'MB',
                'GB',
                'TB',
                'PB',
                'EB',
                'ZB',
                'YB',
            ],
        ];

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f %s", $bytes / pow($mod, $factor), $units[$system][$factor]);
    }
}
