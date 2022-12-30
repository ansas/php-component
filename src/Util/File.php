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

use Ansas\Component\Exception\ContextException;
use Ansas\Component\Exception\IOException;
use InvalidArgumentException;
use SimpleXMLElement;

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
            throw new IOException(sprintf("Cannot chmod %s to %s", $file, $mode));
        }
    }

    /**
     * Creates a file if it does not already exist.
     *
     * @param string $file
     *
     * @throws IOException
     */
    public static function create($file)
    {
        if (!self::exists($file)) {
            self::touch($file);
        }
    }

    /**
     * @param string $file
     *
     * @throws IOException
     */
    public static function getContent($file): string
    {
        return static::getContentPartial($file);
    }

    /**
     * @throws IOException
     */
    public static function getContentPartial(string $file, ?int $length = null, int $offset = 0): string
    {
        if (!self::exists($file)) {
            throw new IOException(sprintf("File %s does not exist", $file));
        }

        $content = file_get_contents($file, false, null, $offset, $length);

        if (false === $content) {
            throw new IOException(sprintf("Cannot get content from file %s", $file));
        }

        return $content;
    }

    /**
     * @param string $file
     * @param string $mode [optional]
     *
     * @return resource
     */
    public static function getStream($file, $mode = 'r')
    {
        if (!self::isReadable($file)) {
            throw new IOException(sprintf("File %s is not readable", $file));
        }

        $stream = fopen($file, $mode);
        if (!$stream) {
            throw new IOException(sprintf("Cannot get stream from file %s", $file));
        }

        return $stream;
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function isReadable($file)
    {
        return self::exists($file) && is_readable($file);
    }

    /**
     * @param string $file
     * @param mixed  $content
     * @param int    $flags [optional]
     *
     * @return int
     * @throws IOException
     */
    public static function putContent($file, $content, $flags = 0)
    {
        $bytesWritten = @file_put_contents($file, $content, $flags);

        if (false === $bytesWritten) {
            throw new IOException(sprintf("Cannot put content to file %s", $file));
        }

        return $bytesWritten;
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
            throw new IOException(sprintf("Cannot copy %s to %s", $pathOld, $pathNew));
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
        if (!self::exists($file)) {
            return;
        }

        if (@unlink($file)) {
            return;
        }

        throw new IOException(sprintf("Cannot delete %s", $file));
    }

    /**
     * Check if file exists and is file.
     *
     * @param string $file
     *
     * @return bool
     */
    public static function exists($file)
    {
        return file_exists($file) && is_file($file);
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
            throw new IOException(sprintf("Old file %s is not a file", $pathOld));
        }

        if (!@rename($pathOld, $pathNew)) {
            throw new IOException(sprintf("Cannot rename %s to %s", $pathOld, $pathNew));
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
    public static function touch($file, $time = null)
    {
        $touch = $time ? @touch($file, $time) : @touch($file);
        if (true !== $touch) {
            throw new IOException(sprintf("Cannot touch %s", $file));
        }
    }

    /**
     * Convert file with json string to array.
     *
     * @throws InvalidArgumentException
     */
    public static function toArray(string $file): array
    {
        return self::toObject($file, true);
    }

    /**
     * Convert file with json string to object or array.
     *
     * @throws InvalidArgumentException|IOException
     */
    public static function toObject(string $file, bool $assoc = false)
    {
        return Text::toObject(self::getContent($file), $assoc);
    }

    /**
     * Convert to xml.
     *
     * @param string $file
     *
     * @return SimpleXMLElement
     * @throws ContextException
     */
    public static function toXml($file)
    {
        return Text::toXml($file, true);
    }

    /**
     * Unzip a file.
     *
     * @param string      $file
     * @param string|null $pathToExtractTo
     *
     * @throws IOException
     */
    public static function unzip($file, $pathToExtractTo = null)
    {
        $call = "ionice -c 3 unzip -q -j -o -DD {$file} 2>&1";
        if ($pathToExtractTo) {
            $call .= " -d {$pathToExtractTo}";
        }

        exec($call, $output, $returnCode);

        if ($returnCode != 0) {
            throw new IOException(sprintf("Cannot unzip %s: %s", $file, join("\n", $output)));
        }
    }
}
