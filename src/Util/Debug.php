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

use Exception;

/**
 * Class Debug
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Debug
{
    /**
     * Check if running in cli modus.
     *
     * @return bool
     */
    public static function isCli()
    {
        return substr(PHP_SAPI, 0, 3) == 'cli';
    }

    /**
     * Print (dump) variable.
     *
     * @param        $data
     * @param string $description [optional]
     * @param string $function    [optional]
     *
     * @throws Exception
     */
    public static function dump($data, $description = null, $function = 'print_r')
    {
        if (!in_array($function, ['print_r', 'var_dump'])) {
            throw new Exception("dump type {$function} not supported");
        }

        if (self::isCli()) {
            if ($description) {
                echo $description . ":\n";
            }
            $function($data);
            if (is_scalar($data)) {
                echo "\n\n";
            }
        } else {
            echo "<pre class=\"debug-dump\">";
            if ($description) {
                echo $description . ":\n";
            }
            $function($data);
            echo "</pre>\n";
        }
    }

    /**
     * Print separator.
     *
     * @param string $char
     * @param int    $repeat
     */
    public static function separator($char = '=', $repeat = 50)
    {
        if (self::isCli()) {
            echo str_repeat($char, $repeat) . "\n";
        } else {
            echo "<div class=\"debug-separator\">" . str_repeat($char, $repeat) . "</div>\n";
        }
    }
}
