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
     * @param      $data
     * @param null $description
     */
    public static function dump($data, $description = null)
    {
        if (self::isCli()) {
            if ($description) {
                echo $description . ":\n";
            }
            print_r($data);
            if (is_scalar($data)) {
                echo "\n\n";
            }
        } else {
            echo "<pre>\n";
            if ($description) {
                echo $description . ":\n";
            }
            print_r($data);
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
        echo str_repeat($char, $repeat) . (self::isCli() ? "\n" : "<br>\n");
    }
}
