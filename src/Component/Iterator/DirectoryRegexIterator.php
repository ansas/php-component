<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Iterator;

use SplFileInfo;

/**
 * Class DirectoryRegexIterator
 *
 * Filters out directories not matching the provided regex. This filter is based on the current directory name only
 * (the whole path is not considered).
 *
 * @package Ansas\Component\Iterator
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class DirectoryRegexIterator extends RegexIterator
{
    /**
     * Filter directory name (without path) against regex.
     *
     * @return bool
     */
    public function accept()
    {
        /** @var SplFileInfo $this */
        return !$this->isDir() || preg_match($this->regex, $this->getFilename());
    }
}
