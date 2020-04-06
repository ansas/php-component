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
     * Get microtime.
     *
     * @return float
     */
    public static function microtime()
    {
        return microtime(true);
    }

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

    /**
     * Sleep for x.x $seconds since last sleep of $timer.
     *
     * @param float  $seconds
     * @param string $timer [optional]
     */
    public static function sleepSinceLast($seconds, $timer = 'default')
    {
        static $sleepTimer = [];

        $lastSlept =& $sleepTimer[$timer] ?? null;

        $seconds = (float) $seconds;

        if ($lastSlept) {
            $seconds -= static::microtime() - $lastSlept;
        }

        static::sleep($seconds);
        $lastSlept = static::microtime();
    }
}
