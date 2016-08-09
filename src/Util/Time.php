<?php

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
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
    public static function sleep(float $seconds)
    {
        if ($seconds > 0) {
            usleep($seconds * 1000000);
        }
    }
}