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
 * Class Random
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Random
{
    /**
     * Set a seed based on params.
     *
     * This makes it possible to create reproducible random values.
     *
     * @param array $params
     *
     * @return void
     */
    public static function setSeedForParams(array $params)
    {
        $seed = 0;

        foreach ($params as $param) {
            if (is_numeric($param)) {
                $seed += floor($param);
            } elseif (is_string($param)) {
                $seed += array_sum(array_map('ord', str_split($param)));
            } else {
                $seed += 1;
            }
        }

        srand($seed);
        mt_srand($seed);
    }
}
