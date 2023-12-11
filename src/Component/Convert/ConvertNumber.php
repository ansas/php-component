<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Convert;

use Exception;

class ConvertNumber
{
    public static function asFloat(mixed $value): float
    {
        if (!$value) {
            return 0;
        }

        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            throw new \Exception('cannot convert to float');
        }

        // convert american or european styled numbers
        $value = str_replace(",",".", (string) $value);
        $value = preg_replace('/\.(?=.*\.)/', '', $value);

        return floatval($value);
    }
}
