<?php

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Ansas\Component\Convert;

/**
 * ConvertToNull
 *
 * This trait can be used to "sanitize values" by setting empty values to null.
 *
 * It holds methods which can be called in order to check if a field is empty
 * and set it to "null" before calling the parent setter method. By doing this
 * we have a more cleaned up object and also prevent e. g. the "versionable"
 * behavior from adding a new version (as "" !== null).
 *
 * You can also provide an array $value and it will be sanitized recursive.
 *
 * @author Ansas Meyer <mail@ansas-meyer.de>
 */
trait ConvertToNull
{
    /**
     * Set empty $value to null
     *
     * @param mixed $value Value to sanitize
     * @param array $considerNull (optional) Values to convert to null
     * @return mixed Sanitized value
     */
    protected function convertEmptyToNull($value, array $considerNull = [''])
    {
        return $this->convertToNull($value, $considerNull);
    }

    /**
     * Set $value to null if matching one of the values of $considerNull list
     *
     * Check on string values is case insensitive (so 'Null' is seen as 'null').
     *
     * @param mixed $value Value to sanitize
     * @param array $considerNull Values to convert to null
     * @param bool $trim Trim string values, default is false
     * @return mixed Sanitized value
     */
    protected function convertToNull($value, array $considerNull, $trim = false)
    {
        if ($value === null) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $value[$subKey] = $this->convertToNull($subValue, $considerNull);
            }
            return $value;
        }

        $compareOriginal = $value;
        if (is_string($value)) {
            if ($trim) {
                $value = trim($value);
            }
            $compareOriginal = mb_strtolower($value);
        }

        foreach ($considerNull as $compareNull) {
            if (is_string($compareNull)) {
                $compareNull = mb_strtolower($compareNull);
            }
            if ($compareOriginal === $compareNull) {
                $value = null;
                break;
            }
        }

        return $value;
    }

    /**
     * Trim $value and also set empty $value to null
     *
     * @param mixed $value Value to sanitize
     * @param array $considerNull (optional) Values to convert to null
     * @return mixed Sanitized value
     */
    protected function trimAndConvertEmptyToNull($value, array $considerNull = [''])
    {
        return $this->convertToNull($value, $considerNull, true);
    }

    /**
     * Trim $value and set to null if matching one of the values of $considerNull list
     *
     * @param mixed $value Value to sanitize
     * @param array $considerNull Values to convert to null
     * @return mixed Sanitized value
     */
    protected function trimAndConvertToNull($value, array $considerNull)
    {
        return $this->convertToNull($value, $considerNull, true);
    }
}
