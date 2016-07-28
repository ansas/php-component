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
use SplFileInfo;

/**
 * Path
 *
 * Util class for path / directory handling
 *
 * @author Ansas Meyer <webmaster@ansas-meyer.de>
 */
class Path
{
    /**
     * Get the "project" root path
     *
     * For an optionally specified $rootForPath (default is start script path)
     * it traverses the path structure until it finds an optionally specified
     * $rootHasDir (default is "lib") and returns it.
     *
     * Throws an \Exception if root path cannot be determined.
     *
     * @param  string $rootForPath (optional) The file or path to get project root path for
     * @param  string $rootHasDir  (optional) The directory that must exist in root path
     * @return string The root path
     * @throws \Exception If root path cannot be determined
     */
    public static function getRoot($rootForPath = null, $rootHasDir = 'lib')
    {
        $rootForPath || $rootForPath = (get_included_files())[0];

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
}
