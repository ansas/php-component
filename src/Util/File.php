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

use Ansas\Component\Exception\IOException;

/**
 * Class File
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class File
{
    /**
     * Copy a (remote) file.
     *
     * @param string $file
     * @param int    $mode
     *
     * @throws IOException
     */
    public static function chmod($file, $mode)
    {
        if (!@chmod($file, $mode)) {
            throw new IOException(sprintf("Cannot chmod %s to %s.", $file, $mode));
        }
    }

    /**
     * Copy a (remote) file.
     *
     * @param string $pathOld
     * @param string $pathNew
     *
     * @throws IOException
     */
    public static function copy($pathOld, $pathNew)
    {
        if (!@copy($pathOld, $pathNew)) {
            throw new IOException(sprintf("Cannot copy %s to %s.", $pathOld, $pathNew));
        }
    }

    /**
     * Delete a file.
     *
     * @param string $file
     *
     * @throws IOException
     */
    public static function delete($file)
    {
        if (!is_file($file)) {
            return;
        }

        if (@unlink($file)) {
            return;
        }

        throw new IOException(sprintf("Cannot delete %s.", $file));
    }

    /**
     * Move / rename a file.
     *
     * @param string $pathOld
     * @param string $pathNew
     *
     * @throws IOException
     */
    public static function move($pathOld, $pathNew)
    {
        if (!is_file($pathOld)) {
            throw new IOException(sprintf("Old file %s is not a file.", $pathOld));
        }

        if (!@rename($pathOld, $pathNew)) {
            throw new IOException(sprintf("Cannot rename %s to %s.", $pathOld, $pathNew));
        }
    }

    /**
     * Touch a file.
     *
     * @param string   $file
     * @param int|null $time
     *
     * @throws IOException
     */
    public static function touch(string $file, int $time = null)
    {
        $touch = $time ? @touch($file, $time) : @touch($file);
        if (true !== $touch) {
            throw new IOException(sprintf("Cannot touch %s.", $file));
        }
    }

    /**
     * Unzip a file.
     *
     * @param string      $file
     * @param string|null $pathToExtractTo
     *
     * @throws IOException
     */
    public static function unzip(string $file, string $pathToExtractTo = null)
    {
        $call = "ionice -c 3 unzip -q -j -o -DD {$file}";
        if ($pathToExtractTo) {
            $call .= " -d {$pathToExtractTo}";
        }

        exec($call, $output, $returnCode);

        if ($returnCode != 0) {
            throw new IOException("Cannot unzip %s: %s", $file, join("\n", $output));
        }
    }
}
