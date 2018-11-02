<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

/**
 * Class Time
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Time
{
    /**
     * Sleep for x.x seconds.
     *
     * @param float $seconds
     */
    public static function sleep($seconds)
    {
        $seconds = (float) $seconds;

        if ($seconds > 0) {
            usleep($seconds * 1000000);
        }
    }
}
