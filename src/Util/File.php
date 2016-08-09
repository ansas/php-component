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

use Exception;

/**
 * Class File
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class File
{
    /**
     * Delete a file.
     *
     * @param string $file
     *
     * @throws Exception
     */
    public static function delete(string $file)
    {
        if (@unlink($file)) {
            return;
        }

        throw new Exception(sprintf("Cannot delete %s.", $file));
    }

    /**
     * Move / rename a file.
     *
     * @param string $pathOld
     * @param string $pathNew
     *
     * @throws Exception
     */
    public static function move(string $pathOld, string $pathNew)
    {
        if (!is_file($pathOld)) {
            throw new Exception(sprintf("Old file %s is not a file.", $pathOld));
        }

        if (!@rename($pathOld, $pathNew)) {
            throw new Exception(sprintf("Cannot rename %s to %s.", $pathOld, $pathNew));
        }
    }

    /**
     * Unzip a file.
     *
     * @param string      $file
     * @param string|null $pathToExtractTo
     *
     * @throws Exception
     */
    public static function unzip(string $file, string $pathToExtractTo = null)
    {
        $call = "ionice -c 3 unzip -q -j -o -DD {$file}";
        if ($pathToExtractTo) {
            $call .= " -d {$pathToExtractTo}";
        }

        exec($call, $output, $returnCode);

        if ($returnCode != 0) {
            throw new Exception("Cannot unzip %s: %s", $file, join("\n", $output));
        }
    }

    /**
     * Touch a file.
     *
     * @param string   $file
     * @param int|null $time
     *
     * @throws Exception
     */
    public function touch(string $file, int $time = null)
    {
        $touch = $time ? @touch($file, $time) : @touch($file);
        if (true !== $touch) {
            throw new Exception(sprintf("Cannot touch %s.", $file));
        }
    }
}