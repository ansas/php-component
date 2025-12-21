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
    public static function microtime(): float
    {
        return microtime(true);
    }

    public static function sleep(float $seconds): void
    {
        if ($seconds > 0) {
            usleep($seconds * 1_000_000);
        }
    }

    public static function sleepRandom(float $min, float $max): void
    {
        static::sleep(rand($min, $max));
    }

    /**
     * Sleep for x.x $seconds since last sleep of $timer.
     */
    public static function sleepSinceLast(float $seconds, string $timer = 'default'): void
    {
        static $sleepTimer = [];

        $lastSlept = $sleepTimer[$timer] ?? null;

        if ($lastSlept) {
            $seconds -= static::microtime() - $lastSlept;
        }

        static::sleep($seconds);
        $sleepTimer[$timer] = static::microtime();
    }
}
