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
use Ansas\Component\Iterator\DirectoryRegexIterator;
use Ansas\Component\Iterator\FileRegexIterator;
use Exception;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Path
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Path
{
    /** flag recursive */
    const RECURSIVE = 1;

    /**
     * Change to (use) directory / path.
     *
     * @param string $path
     *
     * @throws IOException
     */
    public static function chdir($path)
    {
        if (@chdir($path)) {
            return;
        }

        throw new IOException(sprintf("Cannot change to path %s.", $path));
    }

    /**
     * Combine path parts.
     *
     * @param string $part1
     * @param string $part2
     * @param string $separator
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function combine($part1, $part2, $separator = DIRECTORY_SEPARATOR)
    {
        if ($part1 && $part2) {
            $part1 = str_replace('/', $separator, $part1);
            $part1 = str_replace('\\', $separator, $part1);
            $part2 = str_replace('/', $separator, $part2);
            $part2 = str_replace('\\', $separator, $part2);

            return sprintf("%s".$separator."%s", rtrim($part1, $separator), ltrim($part2, $separator));
        }

        throw new InvalidArgumentException("All parts must be filled");
    }

    /**
     * Creates a directory / path if it does not already exist.
     *
     * @param string $path
     * @param int    $mode      [optional]
     * @param bool   $recursive [optional]
     *
     * @throws IOException
     */
    public static function create($path, $mode = 0777, $recursive = true)
    {
        if (@is_dir($path)) {
            return;
        }

        if (@mkdir($path, $mode, $recursive)) {
            return;
        }

        throw new IOException(sprintf("Cannot create path %s.", $path));
    }

    /**
     * Delete a directory / path if it still exists.
     *
     * @param string $path
     * @param bool   $recursive [optional] Path::RECURSIVE
     *
     * @throws IOException
     */
    public static function delete($path, $recursive = false)
    {
        Path::validatePath($path);

        if ($recursive) {
            Path::purge($path);
        }

        if (@rmdir($path)) {
            return;
        }

        throw new IOException(sprintf("Cannot delete path %s.", $path));
    }

    /**
     * Check if path exists and is directory.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path) && is_dir($path);
    }

    /**
     * Change goToPath into go/to/path (GoToPath => /go/to/path)
     *
     * @param string $path
     *
     * @return string
     */
    public static function fromCamelCase($path)
    {
        $path = preg_replace('/[A-Z]/u', '/$0', $path);
        $path = Text::toLower($path);

        return $path;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function isWritable($path)
    {
        return self::exists($path) && is_writable($path);
    }

    /**
     * Change go/to/path into goToPath (/go/to/path => GoToPath)
     *
     * @param string $path
     *
     * @return string
     */
    public static function toCamelCase($path)
    {
        $path = ucwords($path, '/');
        $path = lcfirst($path);
        $path = str_replace('/', '', $path);

        return $path;
    }

    /**
     * Get the "project" root path.
     *
     * For an optionally specified $rootForPath (default is start script path)
     * it traverses the path structure until it finds an optionally specified
     * $rootHasDir (default is "lib") and returns it.
     *
     * Throws an Exception if root path cannot be determined.
     *
     * @param  string $rootForPath [optional] The file or path to get project root path for.
     * @param  string $rootHasDir  [optional] The directory that must exist in root path.
     *
     * @return string The root path
     * @throws Exception If root path cannot be determined
     */
    public static function getRoot($rootForPath = null, $rootHasDir = 'lib')
    {
        if (!$rootForPath) {
            $includes    = get_included_files();
            $rootForPath = $includes[0];
        }

        $rootForPath = rtrim($rootForPath, DIRECTORY_SEPARATOR);
        $rootHasDir  = ltrim($rootHasDir, DIRECTORY_SEPARATOR);

        while (!is_dir($rootForPath . DIRECTORY_SEPARATOR . $rootHasDir)) {
            $rootForPath = dirname($rootForPath);
            if ($rootForPath == DIRECTORY_SEPARATOR) {
                throw new Exception("Cannot determine root path.");
            }
        }

        return $rootForPath;
    }

    /**
     * Delete directory content.
     *
     * @param string       $path
     * @param string|array $fileFilterRegex [optional] Regex of files to purge (can be negated to skip files)
     * @param string|array $dirFilterRegex  [optional] Regex of dirs to purge (can be negated to skip dirs)
     */
    public static function purge($path, $fileFilterRegex = null, $dirFilterRegex = null)
    {
        Path::validatePath($path);

        $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ((array) $fileFilterRegex as $regex) {
            $iterator = new FileRegexIterator($iterator, $regex);
        }

        foreach ((array) $dirFilterRegex as $regex) {
            $iterator = new DirectoryRegexIterator($iterator, $regex);
        }

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                Path::delete($file->getPathName());
                continue;
            }
            File::delete($file->getPathname());
        }
    }

    /**
     * Internal: Validate path.
     *
     * @param $path
     *
     * @throws InvalidArgumentException
     */
    protected static function validatePath($path)
    {
        if (!$path) {
            throw new InvalidArgumentException("Path must not be empty.");
        }
        if ($path == DIRECTORY_SEPARATOR) {
            throw new InvalidArgumentException("Path must not be root path.");
        }
        if ($path == '.' || $path == '..') {
            throw new InvalidArgumentException("Path must not be dot path.");
        }
        if (!is_dir($path)) {
            throw new InvalidArgumentException("Path must be an existing path.");
        }
    }
}
