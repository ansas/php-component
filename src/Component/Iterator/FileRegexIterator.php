<?php

namespace Ansas\Iterator;

/**
 * Class FileRegexIterator
 *
 * Filters out files not matching the provided regex. This filter is based on filename only (directory names are not
 * considered).
 *
 * @package Ansas\Iterator
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
        return !$this->isFile() || preg_match($this->regex, $this->getFilename());
    }
}
