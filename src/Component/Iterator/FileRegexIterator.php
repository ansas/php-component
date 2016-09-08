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
 * Class FileRegexIterator
 *
 * Filters out files not matching the provided regex. This filter is based on filename only (directory names are not
 * considered).
 *
 * @package Ansas\Component\Iterator
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FileRegexIterator extends RegexIterator
{
    /**
     * Filter file name (without path) against regex (but only for files)
     *
     * @return bool
     */
    public function accept()
    {
        /** @var SplFileInfo $this */
        return !$this->isFile() || preg_match($this->regex, $this->getFilename());
    }
}
