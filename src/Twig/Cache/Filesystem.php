<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Twig\Cache;

use Twig_Cache_Filesystem;

/**
 * Class Filesystem
 *
 * Make chmod
 *
 * @package Ansas\Twig\Cache
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Filesystem extends Twig_Cache_Filesystem
{
    protected $umask = 0000;

    /**
     * Create new instance.
     *
     * @param string $directory The root cache directory
     * @param int    $options   [optional] A set of options
     *
     * @return static
     */
    public static function create($directory, $options = 0)
    {
        return new static($directory, $options);
    }

    /**
     * Sets umask.
     *
     * @param int $umask Umask (octal value)
     *
     * @return $this
     */
    public function setUmask($umask)
    {
        $this->umask = $umask;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function write($key, $content)
    {
        $old = umask($this->umask);
        parent::write($key, $content);
        umask($old);
    }
}
