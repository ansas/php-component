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
use FilesystemIterator;
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
     * @throws Exception
     */
    public static function chdir(string $path)
    {
        if (@chdir($path)) {
            return;
        }

        throw new Exception(sprintf("Cannot change to path %s.", $path));
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
     * Creates a directory / path if it does not already exist.
     *
     * @param string $path
     * @param int    $mode      [optional]
     * @param bool   $recursive [optional]
     *
     * @throws Exception
     */
    public static function create(string $path, int $mode = 0777, bool $recursive = true)
    {
        if (@is_dir($path)) {
            return;
        }

        if (@mkdir($path, $mode, $recursive)) {
            return;
        }

        throw new Exception(sprintf("Cannot create path %s.", $path));
    }

    /**
     * Delete a directory / path if it still exists.
     *
     * @param string $path
     * @param bool   $recursive [optional] Path::RECURSIVE
     *
     * @throws Exception
     */
    public static function delete(string $path, bool $recursive = false)
    {
        if (!$path) {
            throw new Exception("Path must not be empty.");
        }
        if ($path == DIRECTORY_SEPARATOR) {
            throw new Exception("Path must not be root path.");
        }
        if ($path == '.' || $path == '..') {
            throw new Exception("Path must not be dot path.");
        }

        if (!is_dir($path)) {
            return;
        }

        if ($recursive) {
            $dir = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                if ($file->isDir()) {
                    Path::delete($file->getPathName());
                    continue;
                }
                File::delete($file->getPathname());
            }
        }

        if (@rmdir($path)) {
            return;
        }

        throw new Exception(sprintf("Cannot delete path %s.", $path));
    }
}
